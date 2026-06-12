import argparse
import uuid
from datetime import datetime

import numpy as np
import pandas as pd
from sklearn.cluster import KMeans
from sklearn.preprocessing import StandardScaler

from db import get_conn


def fetch_semester_bounds(cur, semester_setting_id):
    if semester_setting_id and int(semester_setting_id) > 0:
        cur.execute("SELECT id, start_date, end_date FROM tblsemestersetting WHERE id=%s LIMIT 1", (semester_setting_id,))
        row = cur.fetchone()
        if row:
            return row
    cur.execute("SELECT id, start_date, end_date FROM tblsemestersetting WHERE is_active=1 AND is_deleted=0 ORDER BY id DESC LIMIT 1")
    return cur.fetchone()


def fetch_students(cur, class_id):
    cur.execute(
        """
        SELECT ce.student_id,
               COALESCE(s.id, s2.id) AS sid,
               COALESCE(s.student_number, s2.student_number) AS student_number,
               TRIM(CONCAT(COALESCE(s.last_name, s2.last_name), ', ', COALESCE(s.first_name, s2.first_name))) AS student_name,
               COALESCE(s.user_id, s2.user_id) AS user_id
        FROM tblclassenrollment ce
        LEFT JOIN tblstudent s ON s.id = ce.student_id
        LEFT JOIN tblstudent s2 ON s2.user_id = ce.student_id
        WHERE ce.class_id=%s AND ce.enrollment_status='enrolled'
        """,
        (class_id,),
    )
    return cur.fetchall()


def val_pct(score, mx):
    if mx is None or mx <= 0:
        return 0.0
    if score is None:
        return 0.0
    return float(score) / float(mx) * 100.0


def build_features(cur, class_id, students, sem_start=None, sem_end=None):
    rows = []
    for st in students:
        sid = st['sid']
        if sid is None:
            continue

        # quiz average
        cur.execute(
            """
            SELECT AVG(CASE WHEN COALESCE(qa.max_score,0)>0
                THEN (COALESCE(qa.final_score, qa.manual_score, qa.auto_score, qa.score, 0) / qa.max_score) * 100
                ELSE 0 END) AS v
            FROM tblquizattempt qa
            JOIN tblpost p ON p.id=qa.post_id
            LEFT JOIN tblposttype pt ON pt.id=p.post_type_id
            WHERE p.class_id=%s AND qa.student_id=%s AND COALESCE(pt.type_key,'')='quiz'
              AND qa.status IN ('submitted','graded','returned')
            """,
            (class_id, sid),
        )
        avg_quiz = float((cur.fetchone() or {}).get('v') or 0)

        # exam average
        cur.execute(
            """
            SELECT AVG(CASE WHEN COALESCE(qa.max_score,0)>0
                THEN (COALESCE(qa.final_score, qa.manual_score, qa.auto_score, qa.score, 0) / qa.max_score) * 100
                ELSE 0 END) AS v
            FROM tblquizattempt qa
            JOIN tblpost p ON p.id=qa.post_id
            LEFT JOIN tblposttype pt ON pt.id=p.post_type_id
            WHERE p.class_id=%s AND qa.student_id=%s AND COALESCE(pt.type_key,'')='exam'
              AND qa.status IN ('submitted','graded','returned')
            """,
            (class_id, sid),
        )
        avg_exam = float((cur.fetchone() or {}).get('v') or 0)

        # assignment average
        cur.execute(
            """
            SELECT AVG(CASE WHEN COALESCE(p.points,0)>0 THEN (COALESCE(s.grade,0)/p.points)*100 ELSE COALESCE(s.grade,0) END) AS v
            FROM tblsubmission s
            JOIN tblpost p ON p.id=s.post_id
            LEFT JOIN tblposttype pt ON pt.id=p.post_type_id
            WHERE p.class_id=%s AND s.student_id=%s AND COALESCE(pt.type_key,'')='assignment'
            """,
            (class_id, sid),
        )
        avg_assignment = float((cur.fetchone() or {}).get('v') or 0)

        # activity average
        cur.execute(
            """
            SELECT AVG(CASE WHEN COALESCE(p.points,0)>0 THEN (COALESCE(s.grade,0)/p.points)*100 ELSE COALESCE(s.grade,0) END) AS v
            FROM tblsubmission s
            JOIN tblpost p ON p.id=s.post_id
            LEFT JOIN tblposttype pt ON pt.id=p.post_type_id
            WHERE p.class_id=%s AND s.student_id=%s AND COALESCE(pt.type_key,'')='activity'
            """,
            (class_id, sid),
        )
        avg_activity = float((cur.fetchone() or {}).get('v') or 0)

        # completion + missed
        cur.execute(
            """
            SELECT COUNT(*) AS total
            FROM tblpost p
            LEFT JOIN tblposttype pt ON pt.id=p.post_type_id
            WHERE p.class_id=%s AND p.is_deleted=0 AND p.is_published=1 AND COALESCE(pt.is_gradable,0)=1
            """,
            (class_id,),
        )
        total_gradable = int((cur.fetchone() or {}).get('total') or 0)

        cur.execute(
            """
            SELECT COUNT(DISTINCT s.post_id) AS done_cnt
            FROM tblsubmission s
            JOIN tblpost p ON p.id=s.post_id
            LEFT JOIN tblposttype pt ON pt.id=p.post_type_id
            WHERE p.class_id=%s AND s.student_id=%s AND COALESCE(pt.is_gradable,0)=1
            """,
            (class_id, sid),
        )
        done_cnt = int((cur.fetchone() or {}).get('done_cnt') or 0)

        # include quizzes/exams attempts as completion
        cur.execute(
            """
            SELECT COUNT(DISTINCT qa.post_id) AS done_quiz
            FROM tblquizattempt qa
            JOIN tblpost p ON p.id=qa.post_id
            LEFT JOIN tblposttype pt ON pt.id=p.post_type_id
            WHERE p.class_id=%s AND qa.student_id=%s
              AND COALESCE(pt.type_key,'') IN ('quiz','exam')
              AND qa.status IN ('submitted','graded','returned')
            """,
            (class_id, sid),
        )
        done_quiz = int((cur.fetchone() or {}).get('done_quiz') or 0)

        completed = done_cnt + done_quiz
        if total_gradable <= 0:
            completion_rate = 0.0
            missed = 0
        else:
            completion_rate = min(100.0, (completed / total_gradable) * 100.0)
            missed = max(0, total_gradable - completed)

        # punctuality
        cur.execute(
            """
            SELECT COUNT(*) AS total,
                   SUM(CASE WHEN p.due_date IS NULL OR s.submitted_at <= p.due_date THEN 1 ELSE 0 END) AS ontime
            FROM tblsubmission s
            JOIN tblpost p ON p.id=s.post_id
            WHERE p.class_id=%s AND s.student_id=%s
            """,
            (class_id, sid),
        )
        punc = cur.fetchone() or {}
        sub_total = int(punc.get('total') or 0)
        sub_ontime = int(punc.get('ontime') or 0)
        punctuality = (sub_ontime / sub_total * 100.0) if sub_total > 0 else 0.0

        # attendance
        cur.execute(
            """
            SELECT COUNT(*) AS total,
                   SUM(CASE WHEN ar.status='present' THEN 1 ELSE 0 END) AS present_cnt
            FROM tblattendancerecord ar
            JOIN tblattendance a ON a.id=ar.attendance_id
            WHERE a.class_id=%s AND ar.student_id=%s AND a.is_deleted=0
            """,
            (class_id, sid),
        )
        att = cur.fetchone() or {}
        att_total = int(att.get('total') or 0)
        att_present = int(att.get('present_cnt') or 0)
        attendance_rate = (att_present / att_total * 100.0) if att_total > 0 else 0.0

        # login frequency + engagement from audit trail
        login_frequency = 0.0
        engagement_count = 0
        user_id = st.get('user_id')
        if user_id:
            if sem_start and sem_end:
                cur.execute(
                    """
                    SELECT COUNT(*) AS c
                    FROM tblaudittrail
                    WHERE user_id=%s AND timestamp BETWEEN %s AND DATE_ADD(%s, INTERVAL 1 DAY)
                    """,
                    (user_id, sem_start, sem_end),
                )
            else:
                cur.execute("SELECT COUNT(*) AS c FROM tblaudittrail WHERE user_id=%s", (user_id,))
            engagement_count = int((cur.fetchone() or {}).get('c') or 0)

            # distinct login days approximation
            if sem_start and sem_end:
                cur.execute(
                    """
                    SELECT COUNT(DISTINCT DATE(timestamp)) AS c
                    FROM tblaudittrail
                    WHERE user_id=%s AND action LIKE '%%login%%' AND timestamp BETWEEN %s AND DATE_ADD(%s, INTERVAL 1 DAY)
                    """,
                    (user_id, sem_start, sem_end),
                )
            else:
                cur.execute(
                    "SELECT COUNT(DISTINCT DATE(timestamp)) AS c FROM tblaudittrail WHERE user_id=%s AND action LIKE '%%login%%'",
                    (user_id,),
                )
            login_frequency = float((cur.fetchone() or {}).get('c') or 0)

        engagement_score = (engagement_count * 0.6) + (attendance_rate * 0.2) + (completion_rate * 0.2)

        overall_weighted_grade = (avg_quiz * 0.25) + (avg_assignment * 0.25) + (avg_exam * 0.30) + (avg_activity * 0.20)

        rows.append(
            {
                'student_id': sid,
                'student_number': st.get('student_number') or '',
                'student_name': st.get('student_name') or 'Unknown',
                'avg_quiz_pct': round(avg_quiz, 2),
                'avg_assignment_pct': round(avg_assignment, 2),
                'avg_exam_pct': round(avg_exam, 2),
                'activity_completion_rate': round(completion_rate, 2),
                'submission_punctuality_rate': round(punctuality, 2),
                'attendance_rate': round(attendance_rate, 2),
                'login_frequency': round(login_frequency, 2),
                'engagement_score': round(engagement_score, 2),
                'missed_activities': int(missed),
                'overall_weighted_grade': round(overall_weighted_grade, 2),
            }
        )

    return pd.DataFrame(rows)


def cluster_and_label(df):
    feat_cols = [
        'avg_quiz_pct',
        'avg_assignment_pct',
        'avg_exam_pct',
        'activity_completion_rate',
        'submission_punctuality_rate',
        'attendance_rate',
        'login_frequency',
        'engagement_score',
        'missed_activities',
        'overall_weighted_grade',
    ]

    if df.empty:
        df['cluster_id'] = []
        df['cluster_label'] = []
        df['risk_score'] = []
        df['needs_intervention'] = []
        df['intervention_reason'] = []
        return df

    X = df[feat_cols].fillna(0.0).astype(float)

    # invert missed activities for quality score dimension
    X2 = X.copy()
    X2['missed_activities'] = -X2['missed_activities']

    n_samples = len(X2)
    if n_samples >= 3:
        scaler = StandardScaler()
        Xs = scaler.fit_transform(X2)
        km = KMeans(n_clusters=3, random_state=42, n_init=20)
        cluster_ids = km.fit_predict(Xs)

        centroids = pd.DataFrame(km.cluster_centers_, columns=X2.columns)
        rank_score = centroids.mean(axis=1)
        order = rank_score.sort_values().index.tolist()  # low -> high
        label_map = {
            order[0]: 'at-risk',
            order[1]: 'average-performing',
            order[2]: 'high-performing',
        }
        df['cluster_id'] = cluster_ids
        df['cluster_label'] = [label_map[c] for c in cluster_ids]
    else:
        # fallback for tiny classes
        bins = pd.qcut(df['overall_weighted_grade'].rank(method='first'), q=min(3, n_samples), labels=False)
        # bins: 0 low ...
        df['cluster_id'] = bins.fillna(0).astype(int)
        df['cluster_label'] = df['cluster_id'].map({0: 'at-risk', 1: 'average-performing', 2: 'high-performing'}).fillna('average-performing')

    # risk rule (kmeans + hard thresholds)
    risk = []
    need = []
    reason = []
    for _, r in df.iterrows():
        score = 0.0
        why = []
        if r['cluster_label'] == 'at-risk':
            score += 45
            why.append('Clustered as at-risk')
        if r['attendance_rate'] < 75:
            score += 20
            why.append('Low attendance')
        if r['submission_punctuality_rate'] < 70:
            score += 15
            why.append('Late submissions')
        if r['overall_weighted_grade'] < 75:
            score += 20
            why.append('Low weighted grade')
        if r['missed_activities'] >= 3:
            score += 10
            why.append('Many missed activities')

        score = min(100.0, score)
        flag = 1 if score >= 50 else 0
        risk.append(round(score, 2))
        need.append(flag)
        reason.append('; '.join(why)[:255] if why else None)

    df['risk_score'] = risk
    df['needs_intervention'] = need
    df['intervention_reason'] = reason
    return df


def persist_results(cur, conn, run_id, class_id, df):
    for _, r in df.iterrows():
        snap_id = str(uuid.uuid4())
        cur.execute(
            """
            INSERT INTO tbl_student_analytics_snapshot
            (id, run_id, class_id, student_id, avg_quiz_pct, avg_assignment_pct, avg_exam_pct,
             activity_completion_rate, submission_punctuality_rate, attendance_rate,
             login_frequency, engagement_score, missed_activities, overall_weighted_grade)
            VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
            """,
            (
                snap_id,
                run_id,
                class_id,
                r['student_id'],
                float(r['avg_quiz_pct']),
                float(r['avg_assignment_pct']),
                float(r['avg_exam_pct']),
                float(r['activity_completion_rate']),
                float(r['submission_punctuality_rate']),
                float(r['attendance_rate']),
                float(r['login_frequency']),
                float(r['engagement_score']),
                int(r['missed_activities']),
                float(r['overall_weighted_grade']),
            ),
        )

        cl_id = str(uuid.uuid4())
        cur.execute(
            """
            INSERT INTO tbl_clustering_result
            (id, run_id, class_id, student_id, cluster_id, cluster_label, risk_score, needs_intervention, intervention_reason)
            VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)
            """,
            (
                cl_id,
                run_id,
                class_id,
                r['student_id'],
                int(r['cluster_id']),
                str(r['cluster_label']),
                float(r['risk_score']),
                int(r['needs_intervention']),
                r['intervention_reason'],
            ),
        )

    conn.commit()


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--run_id', required=True)
    parser.add_argument('--class_id', required=True)
    parser.add_argument('--semester_setting_id', required=False, default='0')
    args = parser.parse_args()

    conn = get_conn()
    try:
        with conn.cursor() as cur:
            sem = fetch_semester_bounds(cur, args.semester_setting_id)
            sem_start = sem.get('start_date') if sem else None
            sem_end = sem.get('end_date') if sem else None

            students = fetch_students(cur, args.class_id)
            if len(students) == 0:
                raise ValueError("No enrolled students found for this class")

            df = build_features(cur, args.class_id, students, sem_start, sem_end)
            if df.empty:
                raise ValueError("No analyzable student feature rows found for this class")

            df = cluster_and_label(df)

            persist_results(cur, conn, args.run_id, args.class_id, df)
            print(f"OK run_id={args.run_id} students={len(df)}")
    except Exception as ex:
        conn.rollback()
        raise
    finally:
        conn.close()


if __name__ == '__main__':
    main()
