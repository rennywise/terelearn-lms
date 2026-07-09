import { useState } from "react";

type PostType = "activity" | "quiz" | "lesson" | "announcement";

interface Post {
  id: number;
  type: PostType;
  title: string;
  date: string;
  by: string;
  dueDate?: string;
  overdueDays?: number;
  points?: number;
  mode?: string;
  timePerQ?: string;
  questions?: number;
  submissionStatus?: "not_submitted" | "submitted" | "graded";
  grade?: number;
  fileCount?: number;
  fileName?: string;
  assignmentType?: "individual" | "group";
}

const posts: Post[] = [
  {
    id: 1,
    type: "activity",
    title: "Activity 1",
    date: "May 18, 2026",
    by: "Faculty",
    dueDate: "May 19, 2026",
    overdueDays: 40,
    submissionStatus: "not_submitted",
    assignmentType: "individual",
    points: 25,
  },
  {
    id: 2,
    type: "quiz",
    title: "Quiz 2",
    date: "May 18, 2026",
    by: "Faculty",
    dueDate: "May 18, 2026",
    overdueDays: 42,
    points: 10,
    mode: "Self-paced",
  },
  {
    id: 3,
    type: "quiz",
    title: "Quiz 1",
    date: "May 16, 2026",
    by: "Faculty",
    points: 10,
    mode: "Live mode",
    timePerQ: "45 sec",
    questions: 10,
    submissionStatus: "graded",
    grade: 9,
  },
  {
    id: 4,
    type: "lesson",
    title: "Week 6",
    date: "May 16, 2026",
    by: "Faculty",
    fileName: "WEEK-6-Handling-User-Interactions-a...",
  },
  {
    id: 5,
    type: "lesson",
    title: "Week 5",
    date: "May 16, 2026",
    by: "Faculty",
    fileName: "WEEK-5-Android-UI-Development.pptx",
  },
  {
    id: 6,
    type: "lesson",
    title: "Week 4",
    date: "May 16, 2026",
    by: "Faculty",
    fileName: "WEEK-4-Object-Oriented-Programmin...",
  },
  {
    id: 7,
    type: "lesson",
    title: "Week 3",
    date: "May 16, 2026",
    by: "Faculty",
    fileName: "WEEK-3-Kotlin-Basics-for-Android-Dev...",
  },
];

const typeConfig: Record<PostType, { color: string; bg: string; label: string; icon: string }> = {
  activity: { color: "text-blue-600", bg: "bg-blue-50 border-blue-200", label: "ACTIVITY", icon: "⚡" },
  quiz: { color: "text-purple-600", bg: "bg-purple-50 border-purple-200", label: "QUIZ", icon: "?" },
  lesson: { color: "text-emerald-600", bg: "bg-emerald-50 border-emerald-200", label: "LESSON", icon: "📖" },
  announcement: { color: "text-amber-600", bg: "bg-amber-50 border-amber-200", label: "POST", icon: "📢" },
};

function TypeBadge({ type }: { type: PostType }) {
  const cfg = typeConfig[type];
  return (
    <span className={`inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wide border ${cfg.bg} ${cfg.color}`}>
      {cfg.label}
    </span>
  );
}

function StatusChip({ label, variant }: { label: string; variant: "danger" | "success" | "neutral" | "warning" }) {
  const cls = {
    danger: "bg-red-100 text-red-600 border-red-200",
    success: "bg-emerald-100 text-emerald-700 border-emerald-200",
    neutral: "bg-gray-100 text-gray-500 border-gray-200",
    warning: "bg-amber-100 text-amber-600 border-amber-200",
  }[variant];
  return (
    <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium border ${cls}`}>
      {label}
    </span>
  );
}

function ActivityCard({ post }: { post: Post }) {
  const [expanded, setExpanded] = useState(false);
  return (
    <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
      <div className="p-3">
        <div className="flex items-start justify-between gap-2">
          <div className="flex items-center gap-2 min-w-0">
            <div className="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
              <span className="text-blue-600 text-sm font-bold">A</span>
            </div>
            <div className="min-w-0">
              <div className="flex items-center gap-1.5 flex-wrap">
                <span className="font-semibold text-gray-900 text-sm leading-tight">{post.title}</span>
                <TypeBadge type={post.type} />
              </div>
              <p className="text-[11px] text-gray-400 mt-0.5">{post.date} · {post.by}</p>
            </div>
          </div>
          {post.points && (
            <div className="text-right flex-shrink-0">
              <span className="text-sm font-bold text-gray-800">{post.points}</span>
              <span className="text-[10px] text-gray-400 block">pts</span>
            </div>
          )}
        </div>

        <div className="flex flex-wrap gap-1.5 mt-2.5">
          {post.dueDate && (
            <StatusChip label={`Due ${post.dueDate}`} variant="neutral" />
          )}
          {post.overdueDays && (
            <StatusChip label={`Overdue by ${post.overdueDays}d`} variant="danger" />
          )}
          {post.assignmentType && (
            <StatusChip label={post.assignmentType === "individual" ? "Individual" : "Group"} variant="neutral" />
          )}
          {post.submissionStatus === "not_submitted" && (
            <StatusChip label="Not yet submitted" variant="warning" />
          )}
        </div>

        {post.submissionStatus === "not_submitted" && (
          <button className="mt-2.5 w-full bg-emerald-500 hover:bg-emerald-600 active:bg-emerald-700 text-white text-xs font-semibold py-2 rounded-lg transition-colors">
            Submit Work
          </button>
        )}
      </div>

      <button
        onClick={() => setExpanded(!expanded)}
        className="w-full flex items-center justify-between px-3 py-2 bg-gray-50 border-t border-gray-100 text-[11px] text-gray-400 hover:bg-gray-100 transition-colors"
      >
        <span className="flex items-center gap-1">
          <svg className="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
          Class comments
        </span>
        <svg className={`w-3 h-3 transition-transform ${expanded ? "rotate-180" : ""}`} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
          <path strokeLinecap="round" strokeLinejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
      {expanded && (
        <div className="px-3 py-2 text-[11px] text-gray-400 bg-gray-50">
          No comments yet. Be the first!
        </div>
      )}
    </div>
  );
}

function QuizCard({ post }: { post: Post }) {
  const [expanded, setExpanded] = useState(false);
  const isCompleted = post.submissionStatus === "graded";
  return (
    <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
      <div className="p-3">
        <div className="flex items-start justify-between gap-2">
          <div className="flex items-center gap-2 min-w-0">
            <div className="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
              <span className="text-purple-600 text-sm font-bold">?</span>
            </div>
            <div className="min-w-0">
              <div className="flex items-center gap-1.5 flex-wrap">
                <span className="font-semibold text-gray-900 text-sm leading-tight">{post.title}</span>
                <TypeBadge type={post.type} />
              </div>
              <p className="text-[11px] text-gray-400 mt-0.5">{post.date} · {post.by}</p>
            </div>
          </div>
          {post.points && (
            <div className="text-right flex-shrink-0">
              {isCompleted && post.grade !== undefined ? (
                <>
                  <span className="text-sm font-bold text-emerald-600">{post.grade}/{post.points}</span>
                  <span className="text-[10px] text-gray-400 block">score</span>
                </>
              ) : (
                <>
                  <span className="text-sm font-bold text-gray-800">{post.points}</span>
                  <span className="text-[10px] text-gray-400 block">pts</span>
                </>
              )}
            </div>
          )}
        </div>

        <div className="flex flex-wrap gap-1.5 mt-2.5">
          {post.dueDate && !isCompleted && (
            <StatusChip label={`Due ${post.dueDate}`} variant="neutral" />
          )}
          {post.overdueDays && !isCompleted && (
            <StatusChip label={`Overdue by ${post.overdueDays}d`} variant="danger" />
          )}
          {post.mode && (
            <StatusChip
              label={post.mode}
              variant={post.mode === "Live mode" ? "danger" : "neutral"}
            />
          )}
          {post.timePerQ && (
            <StatusChip label={`${post.timePerQ} / question`} variant="neutral" />
          )}
          {post.questions && (
            <StatusChip label={`${post.questions} questions`} variant="neutral" />
          )}
        </div>

        {isCompleted ? (
          <button className="mt-2.5 w-full bg-gray-800 hover:bg-gray-900 active:bg-black text-white text-xs font-semibold py-2 rounded-lg transition-colors flex items-center justify-center gap-1.5">
            <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Review Results
          </button>
        ) : (
          <button className="mt-2.5 w-full bg-purple-500 hover:bg-purple-600 active:bg-purple-700 text-white text-xs font-semibold py-2 rounded-lg transition-colors">
            Take Quiz
          </button>
        )}
      </div>

      <button
        onClick={() => setExpanded(!expanded)}
        className="w-full flex items-center justify-between px-3 py-2 bg-gray-50 border-t border-gray-100 text-[11px] text-gray-400 hover:bg-gray-100 transition-colors"
      >
        <span className="flex items-center gap-1">
          <svg className="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
          Class comments
        </span>
        <svg className={`w-3 h-3 transition-transform ${expanded ? "rotate-180" : ""}`} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
          <path strokeLinecap="round" strokeLinejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
    </div>
  );
}

function LessonCard({ post }: { post: Post }) {
  const [expanded, setExpanded] = useState(false);
  return (
    <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
      <div className="p-3">
        <div className="flex items-start justify-between gap-2">
          <div className="flex items-center gap-2 min-w-0">
            <div className="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
              <svg className="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
              </svg>
            </div>
            <div className="min-w-0">
              <div className="flex items-center gap-1.5 flex-wrap">
                <span className="font-semibold text-gray-900 text-sm leading-tight">{post.title}</span>
                <TypeBadge type={post.type} />
              </div>
              <p className="text-[11px] text-gray-400 mt-0.5">{post.date} · {post.by}</p>
            </div>
          </div>
        </div>

        <div className="flex flex-wrap gap-1.5 mt-2.5">
          <StatusChip label="Lesson material" variant="neutral" />
          <StatusChip label="Review / download" variant="neutral" />
        </div>

        {post.fileName && (
          <div className="mt-2 flex items-center gap-2 p-2 bg-gray-50 rounded-lg border border-gray-100 cursor-pointer hover:bg-gray-100 transition-colors">
            <svg className="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span className="text-[11px] text-gray-600 truncate flex-1">{post.fileName}</span>
            <svg className="w-3.5 h-3.5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path strokeLinecap="round" strokeLinejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </div>
        )}
      </div>

      <button
        onClick={() => setExpanded(!expanded)}
        className="w-full flex items-center justify-between px-3 py-2 bg-gray-50 border-t border-gray-100 text-[11px] text-gray-400 hover:bg-gray-100 transition-colors"
      >
        <span className="flex items-center gap-1">
          <svg className="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
          Class comments
        </span>
        <svg className={`w-3 h-3 transition-transform ${expanded ? "rotate-180" : ""}`} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
          <path strokeLinecap="round" strokeLinejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
    </div>
  );
}

const filterOptions: { label: string; value: PostType | "all" }[] = [
  { label: "All", value: "all" },
  { label: "Activities", value: "activity" },
  { label: "Quizzes", value: "quiz" },
  { label: "Lessons", value: "lesson" },
];

export function ClassroomUI() {
  const [activeTab, setActiveTab] = useState<"stream" | "files">("stream");
  const [filter, setFilter] = useState<PostType | "all">("all");
  const [search, setSearch] = useState("");
  const [infoExpanded, setInfoExpanded] = useState(false);
  const [meetExpanded, setMeetExpanded] = useState(false);

  const filtered = posts.filter((p) => {
    if (filter !== "all" && p.type !== filter) return false;
    if (search && !p.title.toLowerCase().includes(search.toLowerCase())) return false;
    return true;
  });

  const pendingCount = posts.filter((p) => p.submissionStatus === "not_submitted").length;

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col font-sans max-w-md mx-auto">
      {/* Header */}
      <div className="bg-gradient-to-br from-[#8B0000] via-[#a01515] to-[#c0392b] text-white px-4 pt-4 pb-5 relative overflow-hidden flex-shrink-0">
        <div className="absolute inset-0 opacity-10">
          <div className="absolute top-2 right-4 w-20 h-20 rounded-full border-2 border-white" />
          <div className="absolute bottom-0 right-12 w-32 h-32 rounded-full border border-white" />
        </div>

        {/* Nav row */}
        <div className="flex items-center justify-between mb-3 relative z-10">
          <div className="flex items-center gap-2">
            <button className="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center">
              <svg className="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2.5}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <div className="flex items-center gap-1.5">
              <div className="w-6 h-6 bg-white rounded flex items-center justify-center">
                <span className="text-emerald-700 font-bold text-xs">T</span>
              </div>
              <span className="text-white font-bold text-sm tracking-wide">TERELEARN</span>
            </div>
          </div>
          <div className="flex items-center gap-2">
            {pendingCount > 0 && (
              <div className="flex items-center gap-1 bg-amber-400 text-amber-900 text-[10px] font-bold px-2 py-0.5 rounded-full">
                <span>{pendingCount} pending</span>
              </div>
            )}
            <button className="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center">
              <svg className="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
              </svg>
            </button>
          </div>
        </div>

        {/* Class info */}
        <div className="flex items-center gap-3 relative z-10">
          <div className="w-12 h-12 rounded-full border-2 border-white/60 bg-white/20 flex items-center justify-center flex-shrink-0 overflow-hidden">
            <svg className="w-7 h-7 text-white/70" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
            </svg>
          </div>
          <div className="min-w-0">
            <h1 className="text-white font-bold text-base leading-tight">3-1 Mobile Development</h1>
            <p className="text-white/70 text-[11px] mt-0.5">BS Information Technology</p>
          </div>
        </div>

        {/* Schedule chips */}
        <div className="flex flex-wrap gap-1.5 mt-3 relative z-10">
          {[
            { icon: "📅", label: "1st Sem 2027-2028" },
            { icon: "📆", label: "Fri" },
            { icon: "⏰", label: "7:00 AM – 1:00 PM" },
            { icon: "👤", label: "Harold R. Lucero" },
          ].map((chip) => (
            <div key={chip.label} className="flex items-center gap-1 bg-white/15 backdrop-blur-sm px-2 py-0.5 rounded-full border border-white/20">
              <span className="text-[9px]">{chip.icon}</span>
              <span className="text-white text-[10px] font-medium">{chip.label}</span>
            </div>
          ))}
        </div>
      </div>

      {/* Tabs */}
      <div className="bg-white border-b border-gray-100 px-4 flex-shrink-0">
        <div className="flex gap-6">
          {(["stream", "files"] as const).map((tab) => (
            <button
              key={tab}
              onClick={() => setActiveTab(tab)}
              className={`py-2.5 text-sm font-semibold capitalize border-b-2 transition-colors ${
                activeTab === tab
                  ? "border-emerald-500 text-emerald-600"
                  : "border-transparent text-gray-400 hover:text-gray-600"
              }`}
            >
              {tab}
              {tab === "files" && (
                <span className="ml-1.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold px-1.5 py-0.5 rounded-full">6</span>
              )}
            </button>
          ))}
        </div>
      </div>

      {activeTab === "stream" ? (
        <div className="flex-1 overflow-y-auto">
          {/* Collapsible info cards */}
          <div className="px-3 pt-3 space-y-2">
            {/* Google Meet */}
            <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
              <button
                onClick={() => setMeetExpanded(!meetExpanded)}
                className="w-full flex items-center justify-between px-3 py-2.5"
              >
                <div className="flex items-center gap-2">
                  <div className="w-6 h-6 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg className="w-3.5 h-3.5 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M15 8v8H5V8h10m1-2H4a1 1 0 00-1 1v10a1 1 0 001 1h12a1 1 0 001-1v-3.5l4 4v-11l-4 4V7a1 1 0 00-1-1z" />
                    </svg>
                  </div>
                  <span className="text-xs font-semibold text-gray-700 uppercase tracking-wide">Google Meet</span>
                </div>
                <svg className={`w-3.5 h-3.5 text-gray-400 transition-transform ${meetExpanded ? "rotate-180" : ""}`} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                  <path strokeLinecap="round" strokeLinejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              {meetExpanded && (
                <div className="px-3 pb-3 border-t border-gray-50">
                  <p className="text-xs text-gray-400 mt-2">No Meet link has been posted yet.</p>
                </div>
              )}
            </div>

            {/* Class Info */}
            <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
              <button
                onClick={() => setInfoExpanded(!infoExpanded)}
                className="w-full flex items-center justify-between px-3 py-2.5"
              >
                <div className="flex items-center gap-2">
                  <div className="w-6 h-6 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <svg className="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                      <path strokeLinecap="round" strokeLinejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <span className="text-xs font-semibold text-gray-700 uppercase tracking-wide">Class Info</span>
                </div>
                <svg className={`w-3.5 h-3.5 text-gray-400 transition-transform ${infoExpanded ? "rotate-180" : ""}`} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                  <path strokeLinecap="round" strokeLinejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              {infoExpanded && (
                <div className="px-3 pb-3 border-t border-gray-50 mt-0">
                  <div className="mt-2 space-y-1.5">
                    {[
                      { icon: "🎓", text: "Mobile Development" },
                      { icon: "📚", text: "BS Information Technology" },
                      { icon: "📅", text: "1st Semester 2027-2028" },
                      { icon: "📆", text: "Friday" },
                      { icon: "⏰", text: "07:00 – 13:00" },
                      { icon: "👤", text: "Harold Lucero" },
                    ].map((item) => (
                      <div key={item.text} className="flex items-center gap-2">
                        <span className="text-sm">{item.icon}</span>
                        <span className="text-xs text-gray-600">{item.text}</span>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>
          </div>

          {/* Search + Filter */}
          <div className="px-3 pt-3 pb-2 space-y-2">
            <div className="flex gap-2">
              <div className="flex-1 relative">
                <svg className="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                  <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  placeholder="Search posts..."
                  className="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-400 focus:border-emerald-400 placeholder-gray-400"
                />
              </div>
            </div>

            {/* Filter chips */}
            <div className="flex gap-1.5 overflow-x-auto pb-0.5 no-scrollbar">
              {filterOptions.map((opt) => (
                <button
                  key={opt.value}
                  onClick={() => setFilter(opt.value)}
                  className={`flex-shrink-0 px-3 py-1 rounded-full text-[11px] font-semibold transition-all ${
                    filter === opt.value
                      ? "bg-emerald-500 text-white"
                      : "bg-white text-gray-500 border border-gray-200 hover:border-emerald-300"
                  }`}
                >
                  {opt.label}
                </button>
              ))}
            </div>
          </div>

          {/* Posts */}
          <div className="px-3 pb-6 space-y-2">
            {filtered.length === 0 ? (
              <div className="text-center py-10">
                <p className="text-gray-400 text-sm">No posts found</p>
                <p className="text-gray-300 text-xs mt-1">Try a different filter or search term</p>
              </div>
            ) : (
              filtered.map((post) => {
                if (post.type === "activity") return <ActivityCard key={post.id} post={post} />;
                if (post.type === "quiz") return <QuizCard key={post.id} post={post} />;
                if (post.type === "lesson") return <LessonCard key={post.id} post={post} />;
                return null;
              })
            )}
          </div>
        </div>
      ) : (
        <div className="flex-1 overflow-y-auto px-3 py-4">
          <div className="space-y-2">
            {[
              { name: "WEEK-6-Handling-User-Interactions.pptx", size: "4.2 MB", date: "May 16" },
              { name: "WEEK-5-Android-UI-Development.pptx", size: "3.8 MB", date: "May 16" },
              { name: "WEEK-4-Object-Oriented-Programming.pptx", size: "5.1 MB", date: "May 16" },
              { name: "WEEK-3-Kotlin-Basics-for-Android-Dev.pptx", size: "2.9 MB", date: "May 16" },
              { name: "WEEK-2-Android-Studio-Setup.pdf", size: "1.2 MB", date: "May 9" },
              { name: "WEEK-1-Course-Orientation.pdf", size: "0.8 MB", date: "May 2" },
            ].map((file) => (
              <div key={file.name} className="bg-white rounded-xl border border-gray-100 shadow-sm p-3 flex items-center gap-3 cursor-pointer hover:bg-gray-50 active:bg-gray-100 transition-colors">
                <div className="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
                  <svg className="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-xs font-medium text-gray-800 truncate">{file.name}</p>
                  <p className="text-[10px] text-gray-400 mt-0.5">{file.size} · {file.date}</p>
                </div>
                <svg className="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                  <path strokeLinecap="round" strokeLinejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
