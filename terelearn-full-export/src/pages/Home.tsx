import { useState, useRef, useEffect } from "react";
import { useLocation } from "wouter";
import {
  BookOpen, LayoutDashboard, Archive, User, Settings,
  LogOut, Plus, MoreVertical, Video, Grid2x2, List,
  ChevronDown, Bell, Users, ArrowRight, Copy, Pencil,
  Trash2, Eye, Hash, Calendar, Clock, MapPin, ChevronRight,
} from "lucide-react";
import { CLASSES, ClassData } from "@/data/mock";
import { getActiveSemester } from "@/data/admin-mock";

// ── Card color configs ────────────────────────────────────────────────────────
const CARD_THEMES = [
  { grad: "from-teal-800 via-teal-700 to-teal-600",       ring: "ring-teal-400/30",   accent: "bg-teal-400/20",  dot: "bg-teal-300" },
  { grad: "from-slate-800 via-slate-700 to-slate-600",     ring: "ring-slate-400/30",  accent: "bg-slate-400/20", dot: "bg-slate-300" },
  { grad: "from-rose-900 via-rose-800 to-rose-700",        ring: "ring-rose-400/30",   accent: "bg-rose-400/20",  dot: "bg-rose-300" },
  { grad: "from-violet-900 via-violet-800 to-violet-700",  ring: "ring-violet-400/30", accent: "bg-violet-400/20",dot: "bg-violet-300" },
  { grad: "from-cyan-800 via-cyan-700 to-cyan-600",        ring: "ring-cyan-400/30",   accent: "bg-cyan-400/20",  dot: "bg-cyan-300" },
  { grad: "from-amber-800 via-amber-700 to-amber-600",     ring: "ring-amber-400/30",  accent: "bg-amber-400/20", dot: "bg-amber-300" },
];

// ── Utility: generate class code label ────────────────────────────────────────
function classCode(cls: ClassData, idx: number): string {
  const yr = cls.section.match(/\d/)?.[0] ?? idx + 1;
  const sec = cls.section.split(/\s+/).slice(-1)[0] ?? "A";
  const sub = cls.name.split(" ").map((w) => w[0]).join("").slice(0, 3).toUpperCase();
  return `${yr}–${sec} ${sub}`;
}

// ── Mini avatar stack ─────────────────────────────────────────────────────────
const DEMO_NAMES = ["Ana Reyes", "Ben Santos", "Cris Dela Cruz", "Dan Uy", "Eva Tan"];
function AvatarStack({ count }: { count: number }) {
  const shown = Math.min(count, 4);
  const palette = ["bg-teal-500","bg-violet-500","bg-amber-500","bg-rose-500","bg-cyan-500"];
  if (count === 0) return (
    <span className="text-xs text-white/60 flex items-center gap-1"><Users size={11} /> No students yet</span>
  );
  return (
    <div className="flex items-center gap-2">
      <div className="flex -space-x-2">
        {Array.from({ length: shown }, (_, i) => (
          <div
            key={i}
            title={DEMO_NAMES[i] ?? "Student"}
            className={`w-6 h-6 rounded-full border-2 border-white/30 ${palette[i % palette.length]} flex items-center justify-center text-[9px] font-bold text-white`}
          >
            {DEMO_NAMES[i]?.[0] ?? "S"}
          </div>
        ))}
        {count > 4 && (
          <div className="w-6 h-6 rounded-full border-2 border-white/30 bg-white/20 flex items-center justify-center text-[9px] font-bold text-white">
            +{count - 4}
          </div>
        )}
      </div>
      <span className="text-[11px] text-white/70 font-medium">{count} student{count !== 1 ? "s" : ""}</span>
    </div>
  );
}

// ── Context dropdown menu ─────────────────────────────────────────────────────
function CardMenu({ onOpen }: { onOpen: () => void }) {
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handler = (e: MouseEvent) => {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false);
    };
    document.addEventListener("mousedown", handler);
    return () => document.removeEventListener("mousedown", handler);
  }, []);

  return (
    <div ref={ref} className="relative" onClick={(e) => e.stopPropagation()}>
      <button
        onClick={() => setOpen((o) => !o)}
        className="p-1.5 rounded-full hover:bg-white/20 active:bg-white/30 transition-colors text-white"
      >
        <MoreVertical size={15} />
      </button>
      {open && (
        <div className="absolute right-0 top-8 z-50 bg-white dark:bg-card border border-border rounded-xl shadow-xl py-1 min-w-[160px] animate-in fade-in slide-in-from-top-2 duration-150">
          {[
            { icon: Eye,    label: "View class",   action: onOpen,      color: "text-foreground" },
            { icon: Pencil, label: "Edit class",   action: () => {},    color: "text-foreground" },
            { icon: Copy,   label: "Copy code",    action: () => {},    color: "text-foreground" },
            { icon: Trash2, label: "Delete class", action: () => {},    color: "text-red-500" },
          ].map((item) => {
            const Icon = item.icon;
            return (
              <button
                key={item.label}
                onClick={() => { item.action(); setOpen(false); }}
                className={`w-full flex items-center gap-2.5 px-3 py-2 text-sm ${item.color} hover:bg-muted transition-colors text-left`}
              >
                <Icon size={13} /> {item.label}
              </button>
            );
          })}
        </div>
      )}
    </div>
  );
}

// ── Week progress bar ─────────────────────────────────────────────────────────
function WeekProgress({ currentWeek, totalWeeks }: { currentWeek: number; totalWeeks: number }) {
  const pct = Math.round((currentWeek / totalWeeks) * 100);
  return (
    <div className="space-y-1">
      <div className="flex items-center justify-between">
        <span className="text-[10px] text-white/60 flex items-center gap-1"><Hash size={9} /> Week {currentWeek} of {totalWeeks}</span>
        <span className="text-[10px] text-white/60">{pct}%</span>
      </div>
      <div className="h-1 rounded-full bg-white/10 overflow-hidden">
        <div
          className="h-full rounded-full bg-white/50 transition-all duration-700"
          style={{ width: `${pct}%` }}
        />
      </div>
    </div>
  );
}

// ── Grid card ─────────────────────────────────────────────────────────────────
function GridCard({ cls, idx, onClick }: { cls: ClassData; idx: number; onClick: () => void }) {
  const theme = CARD_THEMES[idx % CARD_THEMES.length];
  const activeSem = getActiveSemester();
  const currentWeek = 4;

  return (
    <div
      onClick={onClick}
      className={`group relative rounded-2xl overflow-hidden border border-white/10 shadow-md
        hover:-translate-y-1.5 hover:shadow-2xl active:scale-[0.98] active:shadow-md
        transition-all duration-200 cursor-pointer select-none ${theme.ring} ring-1`}
    >
      {/* ── Gradient header ── */}
      <div className={`bg-gradient-to-br ${theme.grad} px-4 pt-4 pb-14 relative overflow-hidden`}>
        {/* Decorative blobs */}
        <div className="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-white/5" />
        <div className="absolute -bottom-4 -left-4 w-20 h-20 rounded-full bg-white/5" />

        {/* Top row */}
        <div className="flex items-start justify-between relative z-10">
          <div className="flex-1 min-w-0">
            {/* Active dot */}
            <div className="flex items-center gap-1.5 mb-1.5">
              <span className={`w-2 h-2 rounded-full ${theme.dot} animate-pulse`} />
              <span className="text-[10px] text-white/60 font-medium uppercase tracking-wider">{cls.semester} {cls.schoolYear}</span>
            </div>
            {/* Course code — big */}
            <p className="text-white font-black text-lg leading-tight tracking-tight">
              {classCode(cls, idx)}
            </p>
            <p className="text-white/75 text-[11px] mt-0.5 leading-snug">{cls.name}</p>
          </div>
          <CardMenu onOpen={onClick} />
        </div>

        {/* Meta chips */}
        <div className="flex flex-wrap gap-1.5 mt-3 relative z-10">
          {[
            { icon: Calendar, label: cls.schedule },
            { icon: Clock,    label: `${cls.timeStart}–${cls.timeEnd}` },
            { icon: MapPin,   label: cls.room },
          ].map(({ icon: Icon, label }) => (
            <span key={label} className={`flex items-center gap-1 ${theme.accent} backdrop-blur-sm text-white/90 text-[10px] px-2 py-0.5 rounded-full font-medium`}>
              <Icon size={9} /> {label}
            </span>
          ))}
        </div>

        {/* Week progress */}
        {activeSem && (
          <div className="mt-3 relative z-10">
            <WeekProgress currentWeek={currentWeek} totalWeeks={activeSem.totalWeeks} />
          </div>
        )}

        {/* ── Hover action bar (slides up from bottom of header) ── */}
        <div className="absolute bottom-0 left-0 right-0 flex gap-2 px-4 py-2.5
          translate-y-full group-hover:translate-y-0
          transition-transform duration-200 ease-out z-20
          bg-gradient-to-t from-black/60 to-transparent">
          <button
            onClick={(e) => { e.stopPropagation(); onClick(); }}
            className="flex-1 flex items-center justify-center gap-1.5 py-1.5 bg-white text-gray-900 rounded-lg text-[11px] font-bold hover:bg-white/90 transition-colors"
          >
            Open Class <ArrowRight size={11} />
          </button>
          <button
            onClick={(e) => e.stopPropagation()}
            className="flex items-center gap-1.5 px-3 py-1.5 bg-blue-500 text-white rounded-lg text-[11px] font-bold hover:bg-blue-600 transition-colors"
          >
            <Video size={11} /> Meet
          </button>
        </div>
      </div>

      {/* ── Card footer ── */}
      <div className="bg-card px-4 py-3 flex items-center justify-between -mt-3 rounded-t-2xl relative z-10">
        <AvatarStack count={cls.studentCount} />
        <span className="text-[10px] text-muted-foreground flex items-center gap-1 group-hover:text-primary group-hover:gap-1.5 transition-all">
          View <ChevronRight size={10} className="group-hover:translate-x-0.5 transition-transform" />
        </span>
      </div>
    </div>
  );
}

// ── List row ──────────────────────────────────────────────────────────────────
function ListRow({ cls, idx, onClick }: { cls: ClassData; idx: number; onClick: () => void }) {
  const theme = CARD_THEMES[idx % CARD_THEMES.length];
  const activeSem = getActiveSemester();
  const currentWeek = 4;

  return (
    <div
      onClick={onClick}
      className="group flex items-center gap-4 bg-card border border-border rounded-xl px-4 py-3.5
        hover:border-primary/30 hover:shadow-md hover:-translate-y-0.5 active:scale-[0.995]
        transition-all duration-150 cursor-pointer"
    >
      {/* Color swatch */}
      <div className={`w-1 self-stretch rounded-full bg-gradient-to-b ${theme.grad} flex-shrink-0`} />

      {/* Course code badge */}
      <div className={`w-10 h-10 rounded-xl bg-gradient-to-br ${theme.grad} flex items-center justify-center flex-shrink-0`}>
        <span className="text-white text-[9px] font-black text-center leading-tight px-0.5">
          {classCode(cls, idx).slice(0, 6)}
        </span>
      </div>

      {/* Name + meta */}
      <div className="flex-1 min-w-0">
        <p className="text-sm font-bold text-foreground truncate">{classCode(cls, idx)} — {cls.name}</p>
        <div className="flex items-center gap-3 mt-0.5">
          <span className="text-[10px] text-muted-foreground flex items-center gap-1"><Calendar size={9} /> {cls.schedule}</span>
          <span className="text-[10px] text-muted-foreground flex items-center gap-1"><Clock size={9} /> {cls.timeStart}</span>
          <span className="text-[10px] text-muted-foreground flex items-center gap-1"><MapPin size={9} /> {cls.room}</span>
        </div>
      </div>

      {/* Week progress */}
      {activeSem && (
        <div className="w-28 hidden sm:block">
          <WeekProgress currentWeek={currentWeek} totalWeeks={activeSem.totalWeeks} />
        </div>
      )}

      {/* Students */}
      <div className="hidden md:block">
        <AvatarStack count={cls.studentCount} />
      </div>

      {/* Actions (appear on hover) */}
      <div className="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-150">
        <button
          onClick={(e) => { e.stopPropagation(); }}
          className="px-2.5 py-1.5 bg-blue-100 text-blue-700 rounded-lg text-[10px] font-medium hover:bg-blue-200 transition-colors flex items-center gap-1"
        >
          <Video size={10} /> Meet
        </button>
        <button
          onClick={(e) => { e.stopPropagation(); onClick(); }}
          className="px-2.5 py-1.5 bg-primary text-white rounded-lg text-[10px] font-medium hover:bg-primary/90 transition-colors flex items-center gap-1"
        >
          Open <ArrowRight size={10} />
        </button>
      </div>

      <ChevronRight size={14} className="text-muted-foreground group-hover:text-primary group-hover:translate-x-0.5 transition-all ml-1" />
    </div>
  );
}

// ── Main Home page ────────────────────────────────────────────────────────────
export default function Home() {
  const [, setLocation] = useLocation();
  const [gridView, setGridView] = useState<"grid" | "list">("grid");
  const [sortBy, setSortBy] = useState<"az" | "course">("az");
  const activeSem = getActiveSemester();

  const sorted = [...CLASSES].sort((a, b) =>
    sortBy === "az" ? a.name.localeCompare(b.name) : a.section.localeCompare(b.section)
  );

  return (
    <div className="min-h-screen bg-background flex">
      {/* ── Left sidebar ── */}
      <aside className="w-56 flex-shrink-0 bg-card border-r border-border flex flex-col sticky top-0 h-screen overflow-y-auto">
        {/* Logo */}
        <div className="flex items-center gap-2.5 px-5 py-4 border-b border-border">
          <div className="w-7 h-7 rounded-lg bg-primary flex items-center justify-center">
            <BookOpen size={14} className="text-white" />
          </div>
          <span className="font-black text-base tracking-tight text-foreground">TERELEARN</span>
        </div>

        {/* User block */}
        <div className="flex items-center gap-2.5 px-4 py-3 border-b border-border">
          <div className="w-9 h-9 rounded-full bg-gradient-to-br from-primary to-teal-400 text-white flex items-center justify-center text-xs font-black flex-shrink-0 shadow-sm">
            RL
          </div>
          <div className="min-w-0">
            <p className="text-xs font-semibold text-foreground truncate">Harold Ramirez Lucero</p>
            <p className="text-[10px] text-muted-foreground">Faculty · <span className="text-primary font-semibold">Dean</span></p>
          </div>
        </div>

        {/* Nav */}
        <nav className="flex-1 px-2 py-3 space-y-0.5">
          <p className="px-3 py-1 text-[9px] font-bold text-muted-foreground uppercase tracking-wider">Main</p>
          {[
            { icon: LayoutDashboard, label: "Dashboard", active: false },
            { icon: BookOpen,        label: "My Classes", active: true,  badge: CLASSES.length },
            { icon: Archive,         label: "Archive",    active: false, badge: 0 },
          ].map((item) => {
            const Icon = item.icon;
            return (
              <button
                key={item.label}
                className={`w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm transition-all ${
                  item.active
                    ? "bg-primary text-white shadow-sm shadow-primary/30"
                    : "text-muted-foreground hover:bg-muted hover:text-foreground"
                }`}
              >
                <Icon size={14} />
                <span className="flex-1 text-left font-medium text-xs">{item.label}</span>
                {item.badge !== undefined && item.badge > 0 && (
                  <span className={`text-[10px] min-w-[18px] text-center px-1 py-0.5 rounded-full font-bold ${
                    item.active ? "bg-white/20 text-white" : "bg-muted text-muted-foreground"
                  }`}>
                    {item.badge}
                  </span>
                )}
              </button>
            );
          })}

          <p className="px-3 py-1 mt-4 text-[9px] font-bold text-muted-foreground uppercase tracking-wider">Account</p>
          {[
            { icon: User,     label: "Profile" },
            { icon: Settings, label: "Settings" },
          ].map((item) => {
            const Icon = item.icon;
            return (
              <button key={item.label} className="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs text-muted-foreground hover:bg-muted hover:text-foreground transition-all">
                <Icon size={14} />
                <span className="font-medium">{item.label}</span>
              </button>
            );
          })}

          {/* Switch view */}
          <div className="mt-4 mx-0.5">
            <div className="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-3">
              <p className="text-[9px] font-bold text-amber-700 uppercase tracking-wider mb-2">Switch View</p>
              <button
                onClick={() => setLocation("/admin")}
                className="w-full flex items-center gap-2 bg-amber-500 hover:bg-amber-600 active:scale-95 transition-all text-white rounded-lg px-2.5 py-2 text-xs font-bold shadow-sm shadow-amber-200"
              >
                <LayoutDashboard size={12} />
                <span className="flex-1 text-left">Admin Panel</span>
                <ArrowRight size={10} />
              </button>
              <p className="text-[9px] text-amber-600 mt-1.5 leading-snug">Semesters, sections & week config</p>
            </div>
          </div>
        </nav>

        {/* Sign out */}
        <div className="px-2 py-3 border-t border-border">
          <button className="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-xs text-red-500 hover:bg-red-50 transition-colors font-semibold">
            <LogOut size={13} /> Sign Out
          </button>
        </div>
        <div className="px-4 py-2 text-[9px] text-muted-foreground">Copyright © 2025–2026</div>
      </aside>

      {/* ── Main content ── */}
      <main className="flex-1 min-w-0 flex flex-col">
        {/* Top bar */}
        <div className="sticky top-0 z-30 bg-card/80 backdrop-blur-md border-b border-border px-6 py-3 flex items-center justify-between">
          <div className="flex items-center gap-2 text-xs text-muted-foreground">
            {activeSem && (
              <span className="flex items-center gap-1.5 bg-primary/10 text-primary px-2.5 py-1 rounded-full font-medium">
                <span className="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse" />
                {activeSem.label} · {activeSem.schoolYear} · {activeSem.totalWeeks}wks
              </span>
            )}
          </div>
          <div className="flex items-center gap-2">
            <button className="p-2 rounded-xl hover:bg-muted transition-colors relative">
              <Bell size={15} className="text-muted-foreground" />
              <span className="absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-red-500 rounded-full" />
            </button>
            <div className="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-teal-400 text-white flex items-center justify-center text-xs font-black shadow-sm">RL</div>
          </div>
        </div>

        <div className="p-6 flex-1">
          {/* Page header */}
          <div className="flex items-start justify-between mb-6 flex-wrap gap-3">
            <div>
              <h1 className="text-2xl font-black text-foreground tracking-tight">My Classes</h1>
              <p className="text-sm text-muted-foreground mt-0.5">Admin-assigned and self-created classes</p>
            </div>
            <button className="flex items-center gap-1.5 px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 active:scale-95 transition-all shadow-sm shadow-primary/30">
              <Plus size={14} /> New Class
            </button>
          </div>

          {/* Section header + controls */}
          <div className="flex items-center justify-between mb-4 flex-wrap gap-2">
            <div className="flex items-center gap-2">
              <Users size={12} className="text-muted-foreground" />
              <span className="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">My Created Classes</span>
              <span className="text-[10px] bg-primary/10 text-primary px-1.5 py-0.5 rounded-full font-bold">{CLASSES.length}</span>
            </div>

            <div className="flex items-center gap-1.5">
              <span className="text-[10px] text-muted-foreground">Sort:</span>
              <button
                onClick={() => setSortBy("az")}
                className={`flex items-center gap-1 px-2.5 py-1.5 border rounded-lg text-[10px] font-semibold transition-colors ${
                  sortBy === "az" ? "bg-primary text-white border-primary" : "border-border text-muted-foreground hover:bg-muted"
                }`}
              >
                A–Z
              </button>
              <button
                onClick={() => setSortBy("course")}
                className={`flex items-center gap-1 px-2.5 py-1.5 border rounded-lg text-[10px] font-semibold transition-colors ${
                  sortBy === "course" ? "bg-primary text-white border-primary" : "border-border text-muted-foreground hover:bg-muted"
                }`}
              >
                Course <ChevronDown size={9} />
              </button>
              <div className="w-px h-5 bg-border mx-0.5" />
              {/* Grid/List toggle */}
              <div className="flex items-center gap-0.5 bg-muted rounded-lg p-0.5">
                <button
                  onClick={() => setGridView("grid")}
                  className={`p-1.5 rounded-md transition-all ${gridView === "grid" ? "bg-card text-primary shadow-sm" : "text-muted-foreground hover:text-foreground"}`}
                >
                  <Grid2x2 size={13} />
                </button>
                <button
                  onClick={() => setGridView("list")}
                  className={`p-1.5 rounded-md transition-all ${gridView === "list" ? "bg-card text-primary shadow-sm" : "text-muted-foreground hover:text-foreground"}`}
                >
                  <List size={13} />
                </button>
              </div>
            </div>
          </div>

          {/* ── Cards ── */}
          {gridView === "grid" ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
              {sorted.map((cls, idx) => (
                <GridCard
                  key={cls.id}
                  cls={cls}
                  idx={idx}
                  onClick={() => setLocation(`/class/${cls.id}`)}
                />
              ))}

              {/* Create new */}
              <button
                className="rounded-2xl border-2 border-dashed border-border min-h-[200px]
                  flex flex-col items-center justify-center gap-3
                  hover:border-primary/50 hover:bg-primary/5 active:scale-[0.98]
                  transition-all duration-200 group cursor-pointer"
              >
                <div className="w-12 h-12 rounded-2xl bg-muted group-hover:bg-primary/10 flex items-center justify-center transition-colors">
                  <Plus size={20} className="text-muted-foreground group-hover:text-primary transition-colors" />
                </div>
                <div className="text-center">
                  <p className="text-sm font-semibold text-muted-foreground group-hover:text-primary transition-colors">Create new class</p>
                  <p className="text-[10px] text-muted-foreground mt-0.5">Set up sections, schedule &amp; more</p>
                </div>
              </button>
            </div>
          ) : (
            <div className="space-y-2.5">
              {sorted.map((cls, idx) => (
                <ListRow
                  key={cls.id}
                  cls={cls}
                  idx={idx}
                  onClick={() => setLocation(`/class/${cls.id}`)}
                />
              ))}
              <button
                className="w-full flex items-center gap-3 bg-card border-2 border-dashed border-border rounded-xl px-4 py-3.5
                  hover:border-primary/40 hover:bg-primary/5 active:scale-[0.995] transition-all group"
              >
                <div className="w-10 h-10 rounded-xl bg-muted group-hover:bg-primary/10 flex items-center justify-center transition-colors flex-shrink-0">
                  <Plus size={16} className="text-muted-foreground group-hover:text-primary transition-colors" />
                </div>
                <span className="text-sm font-medium text-muted-foreground group-hover:text-primary transition-colors">Create new class</span>
              </button>
            </div>
          )}
        </div>
      </main>
    </div>
  );
}
