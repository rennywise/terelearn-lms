import { useState } from "react";
import { Download, Search, Info } from "lucide-react";
import { STUDENTS, GRADES } from "@/data/mock";
import { format } from "date-fns";

interface ScoreCell { score: number; total: number }

function getScore(studentId: number, component: string): ScoreCell | null {
  const g = GRADES.find((g) => g.studentId === studentId && g.component === component);
  return g ? { score: g.score, total: g.total } : null;
}

const QUIZ_COLS    = ["quiz1",   "quiz2",    "quiz3",   "quiz4",    "quiz5"] as const;
const EXAM_COLS    = ["prelim",  "midterm",  "finals",  "finals2"] as const;
const ACTIVITY_COLS = ["activity"] as const;

const COL_LABEL: Record<string, string> = {
  quiz1: "Quiz 1", quiz2: "Quiz 2", quiz3: "Quiz 3", quiz4: "Quiz 4", quiz5: "Quiz 5",
  prelim: "Prelim Exam", midterm: "Midterm Exam", finals: "Final Exam", finals2: "Final Exam 2",
  activity: "Activity",
};

const FILTER_OPTIONS = ["All", "Quiz", "Activity", "Assignment", "Exam", "Score"] as const;
type FilterOption = typeof FILTER_OPTIONS[number];

function ScoreDisplay({ cell }: { cell: ScoreCell | null }) {
  if (!cell) return <span className="text-muted-foreground">—</span>;
  const pct = (cell.score / cell.total) * 100;
  const color = pct >= 85 ? "text-green-600" : pct >= 75 ? "text-amber-600" : "text-red-600";
  return (
    <span className={`font-semibold ${color}`}>
      {cell.score.toFixed(cell.score % 1 === 0 ? 0 : 2)}
      <span className="text-[10px] font-normal text-muted-foreground">/{cell.total}</span>
    </span>
  );
}

function getFinalScore(studentId: number): number {
  const allGrades = GRADES.filter((g) => g.studentId === studentId);
  if (!allGrades.length) return 0;
  const sum = allGrades.reduce((s, g) => s + (g.score / g.total) * 100, 0);
  return Math.round((sum / allGrades.length) * 10) / 10;
}

export default function GradesTab({ classId }: { classId: number }) {
  const [filter, setFilter] = useState<FilterOption>("All");
  const [search, setSearch] = useState("");
  const students = STUDENTS.filter((s) => s.classId === classId);
  const filtered = students.filter((s) =>
    s.name.toLowerCase().includes(search.toLowerCase()) ||
    s.studentNumber.includes(search)
  );

  const now = format(new Date(), "yyyy-MM-dd HH:mm:ss");

  const showQuiz     = filter === "All" || filter === "Quiz";
  const showExam     = filter === "All" || filter === "Exam";
  const showActivity = filter === "All" || filter === "Activity";
  const showScore    = filter === "All" || filter === "Score";

  return (
    <div className="px-5 py-5">
      {/* Gradebook header */}
      <div className="bg-card border border-card-border rounded-xl overflow-hidden mb-4">
        <div className="flex items-center justify-between px-4 py-3 border-b border-border">
          <div>
            <div className="flex items-center gap-2">
              <span className="text-base">📊</span>
              <h3 className="text-sm font-bold text-foreground">Gradebook</h3>
            </div>
            <p className="text-[10px] text-muted-foreground mt-0.5">
              Generated: {now} &middot; Students: {students.length} &middot; Read-only from submissions
            </p>
          </div>
          <div className="flex gap-2">
            <button className="flex items-center gap-1.5 px-3 py-1.5 border border-border rounded-lg text-xs text-muted-foreground hover:bg-muted transition-colors">
              <Download size={11} /> PDF
            </button>
            <button className="flex items-center gap-1.5 px-3 py-1.5 border border-border rounded-lg text-xs text-muted-foreground hover:bg-muted transition-colors">
              <Download size={11} /> Excel
            </button>
          </div>
        </div>

        {/* Info banner */}
        <div className="flex items-center gap-2 px-4 py-2.5 bg-blue-50 border-b border-blue-100">
          <Info size={13} className="text-blue-500 flex-shrink-0" />
          <p className="text-[11px] text-blue-700">
            Scores are <strong>read-only</strong> in Gradebook. Values are pulled from graded submissions and quiz attempts.
            To change a score, open the student submission in the related post and grade there.
          </p>
        </div>

        {/* Search + filter bar */}
        <div className="flex items-center gap-3 px-4 py-3 flex-wrap">
          <div className="flex items-center gap-2 bg-background border border-border rounded-lg px-3 py-2 max-w-xs flex-1">
            <Search size={12} className="text-muted-foreground flex-shrink-0" />
            <input
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search by name or student #..."
              className="text-sm bg-transparent outline-none w-full text-foreground placeholder:text-muted-foreground"
            />
          </div>
          <div className="flex gap-1.5 flex-wrap">
            {FILTER_OPTIONS.map((f) => (
              <button
                key={f}
                onClick={() => setFilter(f)}
                className={`px-3 py-1 rounded-full text-xs font-semibold transition-colors border ${
                  filter === f
                    ? f === "All"        ? "bg-gray-800 text-white border-gray-800"
                    : f === "Quiz"       ? "bg-amber-500 text-white border-amber-500"
                    : f === "Activity"   ? "bg-violet-500 text-white border-violet-500"
                    : f === "Assignment" ? "bg-rose-500 text-white border-rose-500"
                    : f === "Exam"       ? "bg-red-600 text-white border-red-600"
                    : "bg-primary text-white border-primary"
                    : "border-border text-muted-foreground hover:bg-muted"
                }`}
              >
                {f}
              </button>
            ))}
            <button className="px-3 py-1 rounded-full text-xs font-semibold border border-border text-muted-foreground hover:bg-muted transition-colors">%</button>
          </div>
        </div>

        {/* Table */}
        <div className="overflow-x-auto">
          <table className="w-full text-sm min-w-[700px]">
            <thead>
              {/* Group header row */}
              <tr className="border-b border-border">
                <th className="text-left text-xs font-medium text-muted-foreground px-3 py-2 w-8 border-r border-border">#</th>
                <th className="text-left text-xs font-medium text-muted-foreground px-3 py-2 w-24 border-r border-border">Student #</th>
                <th className="text-left text-xs font-medium text-muted-foreground px-3 py-2 border-r border-border">Name</th>
                {showQuiz && (
                  <th colSpan={QUIZ_COLS.length} className="text-center text-xs font-bold text-amber-700 bg-amber-50 px-3 py-2 border-r border-amber-200">
                    QUIZ
                  </th>
                )}
                {showActivity && (
                  <th colSpan={ACTIVITY_COLS.length} className="text-center text-xs font-bold text-violet-700 bg-violet-50 px-3 py-2 border-r border-violet-200">
                    ACTIVITY
                  </th>
                )}
                {showExam && (
                  <th colSpan={EXAM_COLS.length} className="text-center text-xs font-bold text-red-700 bg-red-50 px-3 py-2 border-r border-red-100">
                    EXAM
                  </th>
                )}
                {showScore && (
                  <th className="text-center text-xs font-bold text-primary bg-primary/10 px-3 py-2">
                    SCORE
                  </th>
                )}
              </tr>
              {/* Sub-header row */}
              <tr className="border-b border-border bg-muted/30">
                <th className="px-3 py-1.5 border-r border-border" />
                <th className="px-3 py-1.5 border-r border-border" />
                <th className="px-3 py-1.5 border-r border-border" />
                {showQuiz && QUIZ_COLS.map((c) => (
                  <th key={c} className="text-center text-[10px] font-medium text-amber-700 px-3 py-1.5 border-r border-border last:border-amber-200 min-w-[70px]">
                    {COL_LABEL[c]}
                  </th>
                ))}
                {showActivity && ACTIVITY_COLS.map((c) => (
                  <th key={c} className="text-center text-[10px] font-medium text-violet-700 px-3 py-1.5 border-r border-border min-w-[70px]">
                    {COL_LABEL[c]}
                  </th>
                ))}
                {showExam && EXAM_COLS.map((c) => (
                  <th key={c} className="text-center text-[10px] font-medium text-red-700 px-3 py-1.5 border-r border-border last:border-red-100 min-w-[80px]">
                    {COL_LABEL[c]}
                  </th>
                ))}
                {showScore && (
                  <th className="text-center text-[10px] font-medium text-primary px-3 py-1.5 min-w-[70px]">
                    Final
                  </th>
                )}
              </tr>
            </thead>
            <tbody>
              {filtered.map((s, idx) => {
                const finalScore = getFinalScore(s.id);
                return (
                  <tr
                    key={s.id}
                    data-testid={`row-grade-${s.id}`}
                    className="border-b border-border last:border-0 hover:bg-muted/20 transition-colors"
                  >
                    <td className="px-3 py-2.5 text-xs text-muted-foreground border-r border-border">{idx + 1}</td>
                    <td className="px-3 py-2.5 text-xs text-muted-foreground border-r border-border font-mono">{s.studentNumber.split("-")[1]}</td>
                    <td className="px-3 py-2.5 font-semibold text-sm text-foreground border-r border-border">
                      {s.name.split(",").reverse().map((n) => n.trim()).join(", ")}
                    </td>
                    {showQuiz && QUIZ_COLS.map((c) => (
                      <td key={c} className="px-3 py-2.5 text-center text-sm border-r border-border">
                        {/* Mock quiz scores for first 2 */}
                        {c === "quiz1" ? <ScoreDisplay cell={getScore(s.id, "prelim") ? { score: Math.round((getScore(s.id, "prelim")!.score / 100) * 10 * 10) / 10, total: 10 } : null} /> :
                         c === "quiz2" ? <ScoreDisplay cell={getScore(s.id, "midterm") ? { score: Math.round((getScore(s.id, "midterm")!.score / 100) * 20 * 10) / 10, total: 20 } : null} /> :
                         <span className="text-muted-foreground">—</span>}
                      </td>
                    ))}
                    {showActivity && ACTIVITY_COLS.map((c) => (
                      <td key={c} className="px-3 py-2.5 text-center text-sm border-r border-border">
                        <ScoreDisplay cell={getScore(s.id, "activity")} />
                      </td>
                    ))}
                    {showExam && EXAM_COLS.map((c) => (
                      <td key={c} className="px-3 py-2.5 text-center text-sm border-r border-border">
                        {c === "prelim"  ? <ScoreDisplay cell={getScore(s.id, "prelim")} /> :
                         c === "midterm" ? <ScoreDisplay cell={getScore(s.id, "midterm")} /> :
                         <span className="text-muted-foreground">—</span>}
                      </td>
                    ))}
                    {showScore && (
                      <td className="px-3 py-2.5 text-center">
                        <span className={`text-sm font-bold ${finalScore >= 85 ? "text-green-600" : finalScore >= 75 ? "text-amber-600" : "text-red-600"}`}>
                          {finalScore}%
                        </span>
                      </td>
                    )}
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
