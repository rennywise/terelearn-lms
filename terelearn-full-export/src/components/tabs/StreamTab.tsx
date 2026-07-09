import { useState } from "react";
import {
  BookOpen, Zap, HelpCircle, ClipboardList, FileText, Plus,
  Search, SlidersHorizontal, Video, Copy, Link, RefreshCw, Trash2, Pencil,
  X, ChevronDown, Hash, CheckCircle, Info
} from "lucide-react";
import { STREAM_POSTS, ClassData } from "@/data/mock";
import { getWeekOptions, getActiveSemester } from "@/data/admin-mock";
import { formatDistanceToNow } from "date-fns";

const CLASS_CODE = "3309BE4";

// ── Post type config ──────────────────────────────────────────────────────────
const POST_TYPES = [
  { id: "lesson",     label: "Lesson",     icon: BookOpen,      color: "text-teal-700   bg-teal-50   border-teal-200   hover:bg-teal-100" },
  { id: "activity",   label: "Activity",   icon: Zap,           color: "text-violet-700 bg-violet-50 border-violet-200 hover:bg-violet-100" },
  { id: "quiz",       label: "Quiz",       icon: HelpCircle,    color: "text-amber-700  bg-amber-50  border-amber-200  hover:bg-amber-100" },
  { id: "assignment", label: "Assignment", icon: ClipboardList, color: "text-blue-700   bg-blue-50   border-blue-200   hover:bg-blue-100" },
  { id: "exam",       label: "Exam",       icon: FileText,      color: "text-red-700    bg-red-50    border-red-200    hover:bg-red-100" },
  { id: "custom",     label: "Custom",     icon: Plus,          color: "text-green-700  bg-green-50  border-green-200  hover:bg-green-100" },
] as const;

type PostTypeId = typeof POST_TYPES[number]["id"];

const TYPE_BADGE: Record<string, { label: string; color: string }> = {
  lesson:     { label: "LESSON",     color: "bg-teal-500 text-white" },
  activity:   { label: "ACTIVITY",   color: "bg-violet-500 text-white" },
  quiz:       { label: "QUIZ",       color: "bg-amber-500 text-white" },
  assignment: { label: "ASSIGNMENT", color: "bg-blue-500 text-white" },
  exam:       { label: "EXAM",       color: "bg-red-500 text-white" },
  custom:     { label: "POST",       color: "bg-gray-500 text-white" },
};

function Avatar({ name, role }: { name: string; role: string }) {
  const initials = name.split(" ").map((w) => w[0]).join("").slice(0, 2).toUpperCase();
  return (
    <div className={`w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 ${
      role === "faculty" ? "bg-teal-600 text-white" : "bg-accent text-primary"
    }`}>
      {initials}
    </div>
  );
}

// ── Create Post Modal ─────────────────────────────────────────────────────────
function CreatePostModal({
  type, onClose,
}: {
  type: PostTypeId;
  onClose: () => void;
}) {
  const cfg = POST_TYPES.find((t) => t.id === type)!;
  const Icon = cfg.icon;
  const activeSem = getActiveSemester();
  const weekOptions = getWeekOptions();

  const [title, setTitle] = useState("");
  const [week, setWeek] = useState(weekOptions[0] ?? "1st Week");
  const [desc, setDesc] = useState("");
  const [points, setPoints] = useState("");
  const [dueDate, setDueDate] = useState("");
  const [dueTime, setDueTime] = useState("11:59 PM");
  const [attachFile, setAttachFile] = useState(false);

  const isLesson = type === "lesson";
  const isGraded = ["quiz", "assignment", "exam", "activity"].includes(type);

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto">
      <div className="bg-card border border-card-border rounded-2xl shadow-xl w-full max-w-lg my-4">

        {/* Header */}
        <div className={`flex items-center justify-between px-5 py-4 border-b border-border`}>
          <div className="flex items-center gap-2.5">
            <div className={`w-8 h-8 rounded-lg flex items-center justify-center ${cfg.color}`}>
              <Icon size={15} />
            </div>
            <div>
              <h3 className="font-bold text-foreground text-sm">
                Create {cfg.label}
              </h3>
              {activeSem && (
                <p className="text-[10px] text-muted-foreground">
                  {activeSem.label} {activeSem.schoolYear} · {activeSem.totalWeeks} weeks total
                </p>
              )}
            </div>
          </div>
          <button onClick={onClose} className="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground">
            <X size={16} />
          </button>
        </div>

        <div className="px-5 py-5 space-y-4">
          {/* Title */}
          <div>
            <label className="text-xs font-semibold text-foreground mb-1 block">
              {cfg.label} title <span className="text-red-500">*</span>
            </label>
            <input
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              placeholder={`e.g. ${
                type === "lesson"     ? "Introduction to React Hooks" :
                type === "quiz"       ? "Quiz 1 – useState & useEffect" :
                type === "assignment" ? "Activity 1 – Custom Hooks" :
                type === "exam"       ? "Prelim Examination" :
                type === "activity"   ? "Lab Activity 2 – Router" :
                "Custom post title"
              }`}
              className="w-full text-sm border border-input rounded-lg px-3 py-2.5 bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-ring placeholder:text-muted-foreground"
            />
          </div>

          {/* ── Academic Week selector (ADMIN-configured) ── */}
          <div>
            <label className="text-xs font-semibold text-foreground mb-1 flex items-center gap-1.5">
              <Hash size={11} className="text-primary" /> Academic Week
              <span className="text-[10px] text-muted-foreground font-normal ml-1">(admin-configured · up to Week {activeSem?.totalWeeks ?? 18})</span>
            </label>
            <div className="relative">
              <select
                value={week}
                onChange={(e) => setWeek(e.target.value)}
                className="w-full text-sm border border-input rounded-lg px-3 py-2.5 bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-ring appearance-none pr-8"
              >
                {weekOptions.map((w) => (
                  <option key={w} value={w}>{w}</option>
                ))}
              </select>
              <ChevronDown size={13} className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" />
            </div>
            {/* Visual week pills */}
            <div className="flex flex-wrap gap-1 mt-2 max-h-20 overflow-y-auto">
              {weekOptions.map((w, i) => (
                <button
                  key={w}
                  onClick={() => setWeek(w)}
                  className={`text-[9px] px-1.5 py-0.5 rounded font-medium border transition-colors ${
                    week === w
                      ? "bg-primary text-white border-primary"
                      : "bg-muted text-muted-foreground border-border hover:border-primary/40"
                  }`}
                >
                  W{i + 1}
                </button>
              ))}
            </div>
          </div>

          {/* Description */}
          <div>
            <label className="text-xs font-semibold text-foreground mb-1 block">
              {isLesson ? "Lesson content / instructions" : "Description"}
            </label>
            <textarea
              value={desc}
              onChange={(e) => setDesc(e.target.value)}
              placeholder={isLesson ? "Describe the topics, objectives, and learning outcomes..." : "Add instructions or notes..."}
              rows={3}
              className="w-full text-sm border border-input rounded-lg px-3 py-2.5 bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-ring resize-none placeholder:text-muted-foreground"
            />
          </div>

          {/* Graded fields */}
          {isGraded && (
            <div className="grid grid-cols-2 gap-3">
              <div>
                <label className="text-xs font-semibold text-foreground mb-1 block">Points</label>
                <input
                  type="number"
                  value={points}
                  onChange={(e) => setPoints(e.target.value)}
                  placeholder="e.g. 50"
                  className="w-full text-sm border border-input rounded-lg px-3 py-2.5 bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-ring placeholder:text-muted-foreground"
                />
              </div>
              <div>
                <label className="text-xs font-semibold text-foreground mb-1 block">Due date</label>
                <input
                  type="date"
                  value={dueDate}
                  onChange={(e) => setDueDate(e.target.value)}
                  className="w-full text-sm border border-input rounded-lg px-3 py-2.5 bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-ring"
                />
              </div>
            </div>
          )}

          {/* Exam extra fields */}
          {type === "exam" && (
            <div className="bg-red-50 border border-red-200 rounded-xl p-3 space-y-2">
              <p className="text-xs font-semibold text-red-700">Exam settings</p>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="text-[10px] font-semibold text-foreground mb-1 block">Mode</label>
                  <select className="w-full text-xs border border-input rounded-lg px-2 py-1.5 bg-background text-foreground focus:outline-none">
                    <option>Live Mode</option>
                    <option>Scheduled</option>
                    <option>Practice</option>
                  </select>
                </div>
                <div>
                  <label className="text-[10px] font-semibold text-foreground mb-1 block">Time per question</label>
                  <select className="w-full text-xs border border-input rounded-lg px-2 py-1.5 bg-background text-foreground focus:outline-none">
                    <option>30 sec</option>
                    <option>45 sec</option>
                    <option>60 sec</option>
                    <option>No limit</option>
                  </select>
                </div>
              </div>
            </div>
          )}

          {/* Attach file */}
          <div
            className="border-2 border-dashed border-border rounded-xl px-4 py-3 text-center cursor-pointer hover:border-primary/40 hover:bg-primary/5 transition-all"
            onClick={() => setAttachFile(!attachFile)}
          >
            <p className="text-xs text-muted-foreground">📎 Click to attach files, slides, or links</p>
          </div>
        </div>

        {/* Footer */}
        <div className="px-5 py-4 border-t border-border flex items-center gap-2">
          <button onClick={onClose} className="flex-1 py-2 border border-border rounded-lg text-sm text-muted-foreground hover:bg-muted transition-colors">
            Cancel
          </button>
          <button
            disabled={!title.trim()}
            onClick={onClose}
            className={`flex-1 py-2 rounded-lg text-sm font-medium transition-colors ${
              title.trim()
                ? "bg-primary text-white hover:bg-primary/90"
                : "bg-muted text-muted-foreground cursor-not-allowed"
            }`}
          >
            Post {cfg.label}
          </button>
        </div>
      </div>
    </div>
  );
}

// ── Mobile bottom sheet ───────────────────────────────────────────────────────
function MobileSheet({ cls, activeSem, onClose }: {
  cls?: ClassData; activeSem: ReturnType<typeof getActiveSemester>; onClose: () => void;
}) {
  return (
    <>
      {/* backdrop */}
      <div
        className="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden"
        onClick={onClose}
      />
      {/* sheet */}
      <div className="fixed bottom-0 left-0 right-0 z-50 lg:hidden bg-card rounded-t-3xl shadow-2xl border-t border-border animate-in slide-in-from-bottom duration-300">
        {/* drag handle */}
        <div className="flex justify-center pt-3 pb-1">
          <div className="w-10 h-1 rounded-full bg-muted-foreground/30" />
        </div>
        <div className="px-5 pb-8 pt-2 space-y-4">
          <p className="text-sm font-bold text-foreground">Class info</p>

          {/* Meet */}
          <div>
            <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-2">Google Meet</p>
            <a
              href="https://meet.google.com/xkx-stjk-qfi"
              target="_blank"
              rel="noopener noreferrer"
              className="flex items-center justify-center gap-2 w-full py-3 bg-blue-600 text-white rounded-2xl text-sm font-bold hover:bg-blue-700 active:scale-95 transition-all"
            >
              <Video size={16} /> Join Google Meet
            </a>
            <div className="flex items-center gap-2 mt-2 bg-muted rounded-xl px-3 py-2">
              <span className="text-xs text-muted-foreground flex-1 truncate">https://meet.google.com/xkx-stjk-qfi</span>
              <button
                onClick={() => navigator.clipboard?.writeText("https://meet.google.com/xkx-stjk-qfi")}
                className="flex items-center gap-1 text-[10px] text-primary font-medium"
              >
                <Copy size={10} /> Copy
              </button>
            </div>
          </div>

          {/* Class code */}
          <div>
            <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-2">Join Code</p>
            <div className="flex items-center gap-3 bg-primary/5 border border-primary/20 rounded-2xl px-4 py-3">
              <span className="text-2xl font-black tracking-widest text-primary font-mono flex-1">{CLASS_CODE}</span>
              <button
                onClick={() => navigator.clipboard?.writeText(CLASS_CODE)}
                className="flex items-center gap-1.5 px-3 py-1.5 bg-primary text-white rounded-xl text-xs font-bold active:scale-95 transition-all"
              >
                <Copy size={11} /> Copy
              </button>
            </div>
          </div>

          {/* Class details */}
          {cls && (
            <div>
              <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-2">Class Details</p>
              <div className="grid grid-cols-2 gap-2">
                {[
                  { label: "Schedule", value: cls.schedule },
                  { label: "Time",     value: `${cls.timeStart} – ${cls.timeEnd}` },
                  { label: "Room",     value: cls.room },
                  { label: "Semester", value: cls.semester },
                ].map(({ label, value }) => (
                  <div key={label} className="bg-muted/50 rounded-xl px-3 py-2">
                    <p className="text-[9px] font-semibold text-muted-foreground uppercase">{label}</p>
                    <p className="text-xs font-semibold text-foreground mt-0.5">{value}</p>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Academic weeks */}
          {activeSem && (
            <div>
              <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-2">
                Academic Weeks · {activeSem.totalWeeks} total
              </p>
              <div className="flex flex-wrap gap-1">
                {Array.from({ length: activeSem.totalWeeks }, (_, i) => (
                  <span key={i} className="text-[10px] px-2 py-1 bg-primary/10 text-primary rounded-lg font-bold">W{i + 1}</span>
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </>
  );
}

// ── Main StreamTab ─────────────────────────────────────────────────────────────
export default function StreamTab({ classId, cls }: { classId: number; cls?: ClassData }) {
  const [activeModal, setActiveModal] = useState<PostTypeId | null>(null);
  const [filter, setFilter] = useState("all");
  const [search, setSearch] = useState("");
  const [codeTab, setCodeTab] = useState<"code" | "link">("code");
  const [mobileSheet, setMobileSheet] = useState(false);
  const [codeCopied, setCodeCopied] = useState(false);

  const posts = STREAM_POSTS.filter((p) => p.classId === classId);
  const activeSem = getActiveSemester();

  const copyCode = () => {
    navigator.clipboard?.writeText(CLASS_CODE);
    setCodeCopied(true);
    setTimeout(() => setCodeCopied(false), 2000);
  };

  return (
    <div className="flex gap-0 min-h-[calc(100vh-160px)]">
      {activeModal && <CreatePostModal type={activeModal} onClose={() => setActiveModal(null)} />}
      {mobileSheet && <MobileSheet cls={cls} activeSem={activeSem} onClose={() => setMobileSheet(false)} />}

      {/* ── Main feed ── */}
      <div className="flex-1 min-w-0 px-5 py-5 space-y-4">
        {/* Compose area */}
        <div className="bg-card border border-card-border rounded-xl overflow-hidden">
          <div className="flex gap-3 p-4">
            <div className="w-9 h-9 rounded-full bg-teal-600 text-white flex items-center justify-center text-xs font-bold flex-shrink-0">HL</div>
            <button
              onClick={() => setActiveModal("lesson")}
              className="flex-1 text-left text-sm bg-muted rounded-lg px-3 py-2.5 text-muted-foreground hover:bg-muted/70 transition-colors"
            >
              Share with your class...
            </button>
          </div>

          {/* Post type buttons */}
          <div className="flex flex-wrap gap-1.5 px-4 pb-3 border-t border-border pt-3">
            {POST_TYPES.map((t) => {
              const Icon = t.icon;
              return (
                <button
                  key={t.id}
                  onClick={() => setActiveModal(t.id)}
                  className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-medium transition-colors ${t.color}`}
                >
                  <Icon size={11} /> {t.label}
                </button>
              );
            })}
          </div>

          {/* Active week indicator */}
          {activeSem && (
            <div className="px-4 pb-2.5 flex items-center gap-1.5 text-[10px] text-muted-foreground">
              <Hash size={10} className="text-primary" />
              Posts will be tagged to a week (1–{activeSem.totalWeeks}) configured in admin settings
            </div>
          )}
        </div>

        {/* Filter bar */}
        <div className="flex items-center gap-2 flex-wrap">
          <div className="flex items-center gap-2 bg-card border border-border rounded-lg px-3 py-2 flex-1 max-w-xs">
            <Search size={13} className="text-muted-foreground flex-shrink-0" />
            <input
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search post title..."
              className="text-sm bg-transparent outline-none w-full text-foreground placeholder:text-muted-foreground"
            />
          </div>
          <div className="flex items-center gap-1.5 border border-border rounded-lg px-2.5 py-1.5 text-xs text-muted-foreground bg-card">
            <SlidersHorizontal size={11} /> Type
            <select
              value={filter}
              onChange={(e) => setFilter(e.target.value)}
              className="bg-transparent outline-none text-xs ml-1"
            >
              {["all", "lesson", "activity", "quiz", "assignment", "exam"].map((t) => (
                <option key={t} value={t}>{t === "all" ? "All post types" : t.charAt(0).toUpperCase() + t.slice(1)}</option>
              ))}
            </select>
          </div>
        </div>

        {/* Posts */}
        {posts.map((p, idx) => {
          const type = idx === 0 ? "exam" : idx === 1 ? "lesson" : idx % 2 === 0 ? "activity" : "lesson";
          const badge = TYPE_BADGE[type] ?? TYPE_BADGE["lesson"];
          const weekLabel = `${(idx % (activeSem?.totalWeeks ?? 18)) + 1}th Week`;
          return (
            <div key={p.id} className="bg-card border border-card-border rounded-xl overflow-hidden">
              <div className="p-4">
                <div className="flex gap-3">
                  <Avatar name={p.author} role={p.role} />
                  <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2 mb-1 flex-wrap">
                      <span className={`text-[10px] font-bold px-1.5 py-0.5 rounded ${badge.color}`}>{badge.label}</span>
                      <span className="text-[10px] bg-primary/10 text-primary px-1.5 py-0.5 rounded font-medium flex items-center gap-0.5">
                        <Hash size={8} /> {weekLabel}
                      </span>
                      <span className="text-xs text-muted-foreground ml-auto">
                        {formatDistanceToNow(new Date(p.postedAt), { addSuffix: true })} · {p.role === "faculty" ? "Faculty" : "Student"}
                      </span>
                      {p.role === "faculty" && (
                        <div className="flex gap-0.5">
                          <button className="p-1 rounded hover:bg-muted transition-colors text-muted-foreground"><Pencil size={11} /></button>
                          <button className="p-1 rounded hover:bg-muted transition-colors text-red-400"><Trash2 size={11} /></button>
                        </div>
                      )}
                    </div>
                    <p className="text-sm font-semibold text-foreground mb-0.5">{p.author}</p>
                    <p className="text-sm text-foreground/90 leading-relaxed">{p.content}</p>
                  </div>
                </div>
              </div>
              <div className="border-t border-border px-4 py-2">
                <button className="text-xs text-muted-foreground hover:text-foreground flex items-center gap-1 transition-colors">
                  💬 Class comments
                </button>
              </div>
            </div>
          );
        })}
      </div>

      {/* ── Right sidebar ── */}
      <aside className="w-64 flex-shrink-0 border-l border-border py-5 px-4 space-y-4 hidden lg:block">
        {/* JOIN CODE */}
        <div className="bg-card border border-card-border rounded-xl p-4">
          <div className="flex items-center justify-between mb-2">
            <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Join Code</p>
            <button className="p-1 rounded hover:bg-muted transition-colors text-muted-foreground"><RefreshCw size={11} /></button>
          </div>
          <p className="text-2xl font-bold text-primary tracking-widest text-center py-2 font-mono">{CLASS_CODE}</p>
          <div className="flex gap-1.5 mt-1">
            <button
              onClick={() => setCodeTab("code")}
              className={`flex-1 flex items-center justify-center gap-1 py-1.5 rounded-lg text-xs font-medium border transition-colors ${
                codeTab === "code" ? "bg-primary text-white border-primary" : "border-border text-muted-foreground hover:bg-muted"
              }`}
            >
              <Copy size={10} /> Code
            </button>
            <button
              onClick={() => setCodeTab("link")}
              className={`flex-1 flex items-center justify-center gap-1 py-1.5 rounded-lg text-xs font-medium border transition-colors ${
                codeTab === "link" ? "bg-primary text-white border-primary" : "border-border text-muted-foreground hover:bg-muted"
              }`}
            >
              <Link size={10} /> Link
            </button>
          </div>
          {codeTab === "link" && (
            <div className="mt-2 flex items-center gap-1 bg-muted rounded-lg px-2 py-1.5">
              <span className="text-[10px] text-muted-foreground truncate flex-1">http://localhost/terelearn/join/{CLASS_CODE}</span>
              <Copy size={9} className="text-muted-foreground flex-shrink-0" />
            </div>
          )}
        </div>

        {/* GOOGLE MEET */}
        <div className="bg-card border border-card-border rounded-xl p-4">
          <div className="flex items-center justify-between mb-2">
            <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Google Meet</p>
            <button className="p-1 rounded hover:bg-muted transition-colors text-muted-foreground"><Pencil size={11} /></button>
          </div>
          <button className="w-full flex items-center justify-center gap-2 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
            <Video size={13} /> Join Meet
          </button>
          <div className="flex gap-1.5 mt-2">
            <button className="flex-1 flex items-center justify-center gap-1 py-1.5 rounded-lg text-xs border border-border text-muted-foreground hover:bg-muted transition-colors">
              <Copy size={10} /> Copy Link
            </button>
            <button className="flex-1 flex items-center justify-center gap-1 py-1.5 rounded-lg text-xs border border-border text-muted-foreground hover:bg-muted transition-colors">
              ✏️ Change
            </button>
          </div>
          <div className="mt-2 bg-muted rounded-lg px-2 py-1.5">
            <span className="text-[10px] text-muted-foreground">https://meet.google.com/xkx-stjk-qfi</span>
          </div>
        </div>

        {/* CLASS INFO */}
        <div className="bg-card border border-card-border rounded-xl p-4">
          <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-3">Class Info</p>
          {cls && (
            <div className="space-y-1.5 text-xs">
              {[
                ["Semester", `${cls.semester}`],
                ["Year Level", cls.section.match(/\d/)?.[0] ?? "3"],
                ["Schedule", cls.schedule],
                ["Time", `${cls.timeStart} – ${cls.timeEnd}`],
                ["Room", cls.room],
              ].map(([k, v]) => (
                <div key={k} className="flex justify-between">
                  <span className="text-muted-foreground">{k}:</span>
                  <span className="font-medium text-foreground">{v}</span>
                </div>
              ))}
            </div>
          )}
          {activeSem && (
            <div className="mt-3 pt-3 border-t border-border">
              <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-2">Academic Weeks</p>
              <div className="flex items-center justify-between text-xs mb-1">
                <span className="text-muted-foreground">Total weeks:</span>
                <span className="font-bold text-primary">{activeSem.totalWeeks}</span>
              </div>
              <div className="flex flex-wrap gap-1">
                {Array.from({ length: activeSem.totalWeeks }, (_, i) => (
                  <span key={i} className="text-[9px] px-1 py-0.5 bg-primary/10 text-primary rounded font-medium">
                    W{i + 1}
                  </span>
                ))}
              </div>
            </div>
          )}
        </div>
      </aside>

      {/* ── Mobile sticky bottom bar (lg:hidden) ── */}
      <div className="fixed bottom-0 left-0 right-0 z-30 lg:hidden bg-card/95 backdrop-blur-md border-t border-border px-4 py-3 safe-area-inset-bottom">
        <div className="flex items-center gap-2 max-w-lg mx-auto">
          {/* Class code pill */}
          <button
            onClick={copyCode}
            className={`flex items-center gap-2 px-3 py-2.5 rounded-2xl border-2 transition-all active:scale-95 flex-shrink-0 ${
              codeCopied
                ? "border-green-400 bg-green-50 text-green-700"
                : "border-primary/30 bg-primary/5 text-primary"
            }`}
          >
            {codeCopied
              ? <><CheckCircle size={13} /> <span className="text-xs font-bold">Copied!</span></>
              : <><Hash size={13} /> <span className="text-sm font-black tracking-widest font-mono">{CLASS_CODE}</span></>
            }
          </button>

          {/* Join Meet — takes remaining width */}
          <a
            href="https://meet.google.com/xkx-stjk-qfi"
            target="_blank"
            rel="noopener noreferrer"
            className="flex-1 flex items-center justify-center gap-2 py-2.5 bg-blue-600 text-white rounded-2xl text-sm font-bold active:scale-95 hover:bg-blue-700 transition-all shadow-md shadow-blue-600/30"
          >
            <Video size={15} /> Join Meet
          </a>

          {/* Info sheet trigger */}
          <button
            onClick={() => setMobileSheet(true)}
            className="w-11 h-11 rounded-2xl border border-border bg-muted flex items-center justify-center flex-shrink-0 active:scale-95 transition-all"
            title="Class info"
          >
            <Info size={16} className="text-muted-foreground" />
          </button>
        </div>
      </div>

      {/* Spacer so last post isn't behind the bottom bar on mobile */}
      <div className="h-20 lg:hidden" />
    </div>
  );
}
