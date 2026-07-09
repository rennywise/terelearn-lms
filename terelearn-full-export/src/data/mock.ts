export type AttendanceStatus = "present" | "absent" | "late" | "excused";
export type GradeComponent = "prelim" | "midterm" | "finals" | "activity" | "quiz" | "project";
export type Cluster = "high" | "average" | "at_risk";

export interface ClassData {
  id: number;
  name: string;
  section: string;
  program: string;
  semester: string;
  schoolYear: string;
  schedule: string;
  timeStart: string;
  timeEnd: string;
  room: string;
  studentCount: number;
}

export interface Student {
  id: number;
  classId: number;
  name: string;
  studentNumber: string;
  email: string;
}

export interface Session {
  id: number;
  classId: number;
  date: string;
  topics: string;
  notes?: string;
}

export interface AttendanceRecord {
  id: number;
  sessionId: number;
  studentId: number;
  status: AttendanceStatus;
  remarks?: string;
}

export interface GradeEntry {
  id: number;
  classId: number;
  studentId: number;
  component: GradeComponent;
  score: number;
  total: number;
  remarks?: string;
}

export interface StreamPost {
  id: number;
  classId: number;
  author: string;
  role: "faculty" | "student";
  content: string;
  postedAt: string;
  attachments?: string[];
}

// ── Classes ──────────────────────────────────────────────────────────────────
export const CLASSES: ClassData[] = [
  {
    id: 1,
    name: "Application Development 1",
    section: "BSIT 3-1",
    program: "Bachelor of Science in Information Technology",
    semester: "1st Semester",
    schoolYear: "2027–2028",
    schedule: "Tue, Fri",
    timeStart: "7:30 AM",
    timeEnd: "1:00 PM",
    room: "Lab 302",
    studentCount: 8,
  },
  {
    id: 2,
    name: "Web Systems & Technologies",
    section: "BSIT 2-2",
    program: "Bachelor of Science in Information Technology",
    semester: "1st Semester",
    schoolYear: "2027–2028",
    schedule: "Mon, Wed, Thu",
    timeStart: "9:00 AM",
    timeEnd: "11:00 AM",
    room: "Lab 201",
    studentCount: 12,
  },
  {
    id: 3,
    name: "Database Management Systems",
    section: "BSIT 2-1",
    program: "Bachelor of Science in Information Technology",
    semester: "1st Semester",
    schoolYear: "2027–2028",
    schedule: "Tue, Thu",
    timeStart: "1:00 PM",
    timeEnd: "3:00 PM",
    room: "Room 105",
    studentCount: 10,
  },
];

// ── Students (class 1) ────────────────────────────────────────────────────────
export const STUDENTS: Student[] = [
  { id: 1, classId: 1, name: "Alvarez, Maria Santos", studentNumber: "2024-00112", email: "m.alvarez@student.edu" },
  { id: 2, classId: 1, name: "Bautista, Juan Carlos", studentNumber: "2024-00134", email: "j.bautista@student.edu" },
  { id: 3, classId: 1, name: "Cruz, Ana Reyes", studentNumber: "2024-00158", email: "a.cruz@student.edu" },
  { id: 4, classId: 1, name: "Dela Rosa, Paolo Miguel", studentNumber: "2024-00179", email: "p.delarosa@student.edu" },
  { id: 5, classId: 1, name: "Garcia, Lena Marie", studentNumber: "2024-00201", email: "l.garcia@student.edu" },
  { id: 6, classId: 1, name: "Hernandez, Mark Anthony", studentNumber: "2024-00223", email: "m.hernandez@student.edu" },
  { id: 7, classId: 1, name: "Lopez, Carla Joy", studentNumber: "2024-00245", email: "c.lopez@student.edu" },
  { id: 8, classId: 1, name: "Mendoza, Ryan James", studentNumber: "2024-00267", email: "r.mendoza@student.edu" },
];

// ── Sessions (June 2026 — class 1) ───────────────────────────────────────────
export const SESSIONS: Session[] = [
  { id: 1, classId: 1, date: "2026-06-03", topics: "Introduction to React Hooks; useState & useEffect patterns" },
  { id: 2, classId: 1, date: "2026-06-06", topics: "Component lifecycle and custom hooks" },
  { id: 3, classId: 1, date: "2026-06-10", topics: "Context API and state management fundamentals" },
  { id: 4, classId: 1, date: "2026-06-13", topics: "React Router v6 — nested routes and layouts" },
  { id: 5, classId: 1, date: "2026-06-17", topics: "API integration with React Query; data fetching patterns" },
  { id: 6, classId: 1, date: "2026-06-20", topics: "Form handling with react-hook-form and Zod validation" },
  { id: 7, classId: 1, date: "2026-06-24", topics: "Performance optimization — memo, useMemo, useCallback" },
  { id: 8, classId: 1, date: "2026-06-27", topics: "Project workshop: sprint planning and team review" },
];

// ── Attendance ────────────────────────────────────────────────────────────────
// status map per session (rows = students 1-8, cols = sessions 1-8)
const ATT_MAP: AttendanceStatus[][] = [
  //  s1        s2        s3        s4        s5        s6        s7        s8
  ["present","present","present","present","present","present","present","present"], // Maria
  ["present","absent", "present","late",   "present","present","absent", "present"], // Juan
  ["present","present","present","present","absent", "present","present","present"], // Ana
  ["absent", "present","absent", "present","present","present","present","late"  ], // Paolo
  ["present","present","present","present","present","present","present","present"], // Lena
  ["present","late",   "present","present","absent", "absent", "present","present"], // Mark
  ["present","present","absent", "present","present","late",   "present","present"], // Carla
  ["late",   "present","present","present","present","present","present","absent" ], // Ryan
];

export const ATTENDANCE: AttendanceRecord[] = [];
let attId = 1;
ATT_MAP.forEach((studentRow, si) => {
  studentRow.forEach((status, sessionIdx) => {
    ATTENDANCE.push({
      id: attId++,
      sessionId: SESSIONS[sessionIdx].id,
      studentId: STUDENTS[si].id,
      status,
    });
  });
});

// ── Grades ────────────────────────────────────────────────────────────────────
export const GRADES: GradeEntry[] = [
  // Prelim scores
  { id: 1,  classId: 1, studentId: 1, component: "prelim",   score: 92, total: 100 },
  { id: 2,  classId: 1, studentId: 2, component: "prelim",   score: 75, total: 100 },
  { id: 3,  classId: 1, studentId: 3, component: "prelim",   score: 88, total: 100 },
  { id: 4,  classId: 1, studentId: 4, component: "prelim",   score: 71, total: 100 },
  { id: 5,  classId: 1, studentId: 5, component: "prelim",   score: 95, total: 100 },
  { id: 6,  classId: 1, studentId: 6, component: "prelim",   score: 63, total: 100 },
  { id: 7,  classId: 1, studentId: 7, component: "prelim",   score: 80, total: 100 },
  { id: 8,  classId: 1, studentId: 8, component: "prelim",   score: 78, total: 100 },
  // Midterm
  { id: 9,  classId: 1, studentId: 1, component: "midterm",  score: 89, total: 100 },
  { id: 10, classId: 1, studentId: 2, component: "midterm",  score: 68, total: 100 },
  { id: 11, classId: 1, studentId: 3, component: "midterm",  score: 91, total: 100 },
  { id: 12, classId: 1, studentId: 4, component: "midterm",  score: 74, total: 100 },
  { id: 13, classId: 1, studentId: 5, component: "midterm",  score: 97, total: 100 },
  { id: 14, classId: 1, studentId: 6, component: "midterm",  score: 58, total: 100 },
  { id: 15, classId: 1, studentId: 7, component: "midterm",  score: 84, total: 100 },
  { id: 16, classId: 1, studentId: 8, component: "midterm",  score: 76, total: 100 },
  // Activity
  { id: 17, classId: 1, studentId: 1, component: "activity", score: 48, total: 50 },
  { id: 18, classId: 1, studentId: 2, component: "activity", score: 38, total: 50 },
  { id: 19, classId: 1, studentId: 3, component: "activity", score: 45, total: 50 },
  { id: 20, classId: 1, studentId: 4, component: "activity", score: 37, total: 50 },
  { id: 21, classId: 1, studentId: 5, component: "activity", score: 50, total: 50 },
  { id: 22, classId: 1, studentId: 6, component: "activity", score: 30, total: 50 },
  { id: 23, classId: 1, studentId: 7, component: "activity", score: 42, total: 50 },
  { id: 24, classId: 1, studentId: 8, component: "activity", score: 40, total: 50 },
];

// ── Stream ────────────────────────────────────────────────────────────────────
export const STREAM_POSTS: StreamPost[] = [
  {
    id: 1,
    classId: 1,
    author: "Renwer Lucero",
    role: "faculty",
    content: "Welcome to Application Development 1! Please review the course syllabus posted in the Files section. Our first session will focus on React Hooks fundamentals. Come prepared!",
    postedAt: "2026-06-01T08:00:00Z",
  },
  {
    id: 2,
    classId: 1,
    author: "Maria Santos Alvarez",
    role: "student",
    content: "Good morning! Will the Activity 1 deadline be extended? Some of us are still working on the useEffect implementation.",
    postedAt: "2026-06-05T09:15:00Z",
  },
  {
    id: 3,
    classId: 1,
    author: "Renwer Lucero",
    role: "faculty",
    content: "Activity 1 deadline is extended until June 8, 11:59 PM. Submit via the Classwork section. Make sure to include your component diagram.",
    postedAt: "2026-06-05T10:30:00Z",
  },
  {
    id: 4,
    classId: 1,
    author: "Lena Marie Garcia",
    role: "student",
    content: "Thank you, Sir! Quick question — should we use useState or useReducer for the cart feature in Activity 2?",
    postedAt: "2026-06-09T14:20:00Z",
  },
  {
    id: 5,
    classId: 1,
    author: "Renwer Lucero",
    role: "faculty",
    content: "Either works — but useReducer is preferred when you have complex state transitions with multiple sub-values. We'll cover this in detail on June 10. See you then!",
    postedAt: "2026-06-09T15:45:00Z",
  },
];

// ── Helpers ───────────────────────────────────────────────────────────────────
export function getAttendanceForSession(sessionId: number): AttendanceRecord[] {
  return ATTENDANCE.filter((a) => a.sessionId === sessionId);
}

export function getAttendanceForStudent(studentId: number): AttendanceRecord[] {
  return ATTENDANCE.filter((a) => a.studentId === studentId);
}

export function getSessionAttendancePct(sessionId: number): number {
  const recs = getAttendanceForSession(sessionId);
  if (!recs.length) return 0;
  const present = recs.filter((r) => r.status === "present" || r.status === "late").length;
  return Math.round((present / recs.length) * 100);
}

export function getStudentAttendancePct(studentId: number): number {
  const recs = getAttendanceForStudent(studentId);
  if (!recs.length) return 0;
  const present = recs.filter((r) => r.status === "present" || r.status === "late").length;
  return Math.round((present / recs.length) * 100);
}

export function getStudentGradeAverage(studentId: number): number {
  const grades = GRADES.filter((g) => g.studentId === studentId);
  if (!grades.length) return 0;
  const total = grades.reduce((sum, g) => sum + (g.score / g.total) * 100, 0);
  return Math.round((total / grades.length) * 10) / 10;
}

export function computeKMeans(): Array<{
  student: Student;
  attendancePct: number;
  gradeAvg: number;
  cluster: Cluster;
}> {
  const data = STUDENTS.map((s) => ({
    student: s,
    attendancePct: getStudentAttendancePct(s.id),
    gradeAvg: getStudentGradeAverage(s.id),
    cluster: "average" as Cluster,
  }));

  // Simple K-Means with 3 clusters
  // Initialize centroids: high (90att,90grade), avg (75,75), risk (55,55)
  let centroids = [
    { att: 90, grade: 90 },
    { att: 75, grade: 75 },
    { att: 55, grade: 55 },
  ];

  for (let iter = 0; iter < 10; iter++) {
    // Assign
    data.forEach((d) => {
      const dists = centroids.map((c) =>
        Math.sqrt(Math.pow(d.attendancePct - c.att, 2) + Math.pow(d.gradeAvg - c.grade, 2))
      );
      const minIdx = dists.indexOf(Math.min(...dists));
      d.cluster = (["high", "average", "at_risk"] as Cluster[])[minIdx];
    });
    // Recompute centroids
    centroids = (["high", "average", "at_risk"] as Cluster[]).map((label) => {
      const group = data.filter((d) => d.cluster === label);
      if (!group.length) return centroids[(["high","average","at_risk"] as Cluster[]).indexOf(label)];
      return {
        att: group.reduce((s, d) => s + d.attendancePct, 0) / group.length,
        grade: group.reduce((s, d) => s + d.gradeAvg, 0) / group.length,
      };
    });
  }

  return data;
}

export function getAttendanceSummaryByDate() {
  return SESSIONS.map((s) => {
    const pct = getSessionAttendancePct(s.id);
    return { sessionId: s.id, date: s.date, topics: s.topics, pct };
  });
}

export function getAttendanceFlags() {
  return STUDENTS.map((s) => ({
    student: s,
    pct: getStudentAttendancePct(s.id),
    absences: ATTENDANCE.filter((a) => a.studentId === s.id && a.status === "absent").length,
  })).filter((f) => f.pct < 80);
}
