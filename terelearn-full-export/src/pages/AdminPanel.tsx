import { useState } from "react";
import { useLocation } from "wouter";
import {
  BookOpen, LayoutDashboard, Users, Building2, GraduationCap,
  Grid3x3, CalendarRange, Calendar, LogOut, ChevronRight,
  Moon, Bell, Plus, Pencil, Trash2, CheckCircle, MoreVertical,
  ChevronDown, Settings2, Clock, Hash, Sun,
} from "lucide-react";
import { SEMESTERS, PROGRAMS, DEPARTMENTS, Semester, Program } from "@/data/admin-mock";

type AdminPage =
  | "dashboard"
  | "accounts"
  | "departments"
  | "programs"
  | "year-sections"
  | "semester-settings"
  | "calendar";

// ── Shared badge ─────────────────────────────────────────────────────────────
function Badge({ children, color = "teal" }: { children: React.ReactNode; color?: string }) {
  const map: Record<string, string> = {
    teal:   "bg-teal-100 text-teal-700 border-teal-200",
    violet: "bg-violet-100 text-violet-700 border-violet-200",
    amber:  "bg-amber-100 text-amber-700 border-amber-200",
    green:  "bg-green-100 text-green-700 border-green-200",
    gray:   "bg-muted text-muted-foreground border-border",
  };
  return (
    <span className={`inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border ${map[color] ?? map.gray}`}>
      {children}
    </span>
  );
}

// ── Semester settings page ────────────────────────────────────────────────────
function SemesterSettings() {
  const [semesters, setSemesters] = useState<Semester[]>(SEMESTERS);
  const [showModal, setShowModal] = useState(false);
  const [editTarget, setEditTarget] = useState<Semester | null>(null);
  const [dark, setDark] = useState(false);

  // Form state
  const [form, setForm] = useState<Partial<Semester>>({
    label: "1st Semester", schoolYear: "", startDate: "", endDate: "", totalWeeks: 18, isActive: false,
  });

  const active = semesters.find((s) => s.isActive);

  const openAdd = () => {
    setForm({ label: "1st Semester", schoolYear: "", startDate: "", endDate: "", totalWeeks: 18, isActive: false });
    setEditTarget(null);
    setShowModal(true);
  };
  const openEdit = (s: Semester) => {
    setForm({ ...s });
    setEditTarget(s);
    setShowModal(true);
  };
  const handleSave = () => {
    if (editTarget) {
      setSemesters((prev) => prev.map((s) => s.id === editTarget.id ? { ...s, ...form } as Semester : s));
    } else {
      setSemesters((prev) => [...prev, { id: Date.now(), isActive: false, ...form } as Semester]);
    }
    setShowModal(false);
  };
  const handleSetActive = (id: number) => {
    setSemesters((prev) => prev.map((s) => ({ ...s, isActive: s.id === id })));
  };
  const handleDelete = (id: number) => {
    setSemesters((prev) => prev.filter((s) => s.id !== id));
  };

  return (
    <div className="space-y-5">
      {/* Modal */}
      {showModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
          <div className="bg-card border border-card-border rounded-2xl shadow-xl w-full max-w-md">
            <div className="flex items-center justify-between px-6 py-4 border-b border-border">
              <h3 className="font-bold text-foreground">{editTarget ? "Edit Period" : "Add New Period"}</h3>
              <button onClick={() => setShowModal(false)} className="text-muted-foreground hover:text-foreground text-xl leading-none">&times;</button>
            </div>
            <div className="px-6 py-5 space-y-4">
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="text-xs font-semibold text-foreground mb-1 block">Semester</label>
                  <select
                    value={form.label}
                    onChange={(e) => setForm({ ...form, label: e.target.value })}
                    className="w-full text-sm border border-input rounded-lg px-3 py-2 bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-ring"
                  >
                    <option>1st Semester</option>
                    <option>2nd Semester</option>
                    <option>Summer</option>
                  </select>
                </div>
                <div>
                  <label className="text-xs font-semibold text-foreground mb-1 block">School Year</label>
                  <input
                    value={form.schoolYear}
                    onChange={(e) => setForm({ ...form, schoolYear: e.target.value })}
                    placeholder="e.g. 2027-2028"
                    className="w-full text-sm border border-input rounded-lg px-3 py-2 bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-ring placeholder:text-muted-foreground"
                  />
                </div>
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="text-xs font-semibold text-foreground mb-1 block">Start Date</label>
                  <input
                    type="date"
                    value={form.startDate}
                    onChange={(e) => setForm({ ...form, startDate: e.target.value })}
                    className="w-full text-sm border border-input rounded-lg px-3 py-2 bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-ring"
                  />
                </div>
                <div>
                  <label className="text-xs font-semibold text-foreground mb-1 block">End Date</label>
                  <input
                    type="date"
                    value={form.endDate}
                    onChange={(e) => setForm({ ...form, endDate: e.target.value })}
                    className="w-full text-sm border border-input rounded-lg px-3 py-2 bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-ring"
                  />
                </div>
              </div>

              {/* ── Week configuration ── */}
              <div className="bg-primary/5 border border-primary/20 rounded-xl p-4">
                <div className="flex items-center gap-2 mb-3">
                  <Hash size={14} className="text-primary" />
                  <span className="text-sm font-semibold text-primary">Academic Week Configuration</span>
                </div>
                <label className="text-xs font-semibold text-foreground mb-1 block">
                  Total number of academic weeks
                </label>
                <div className="flex items-center gap-3">
                  <input
                    type="number"
                    min={1}
                    max={30}
                    value={form.totalWeeks}
                    onChange={(e) => setForm({ ...form, totalWeeks: Number(e.target.value) })}
                    className="w-24 text-sm border border-input rounded-lg px-3 py-2 bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-ring"
                  />
                  <span className="text-xs text-muted-foreground">weeks in this semester</span>
                </div>
                <div className="mt-3 flex flex-wrap gap-1.5">
                  {Array.from({ length: form.totalWeeks ?? 18 }, (_, i) => (
                    <span key={i} className="text-[9px] px-1.5 py-0.5 bg-primary/10 text-primary rounded font-medium">
                      {i + 1}{i === 0 ? "st" : i === 1 ? "nd" : i === 2 ? "rd" : "th"} Week
                    </span>
                  ))}
                </div>
                <p className="text-[10px] text-muted-foreground mt-2">
                  Faculty will see these week options when creating lessons and logging sessions.
                </p>
              </div>
            </div>
            <div className="px-6 py-4 border-t border-border flex gap-2">
              <button onClick={() => setShowModal(false)} className="flex-1 py-2 border border-border rounded-lg text-sm text-muted-foreground hover:bg-muted transition-colors">Cancel</button>
              <button onClick={handleSave} className="flex-1 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors">
                {editTarget ? "Save changes" : "Add period"}
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Page header */}
      <div className="flex items-center justify-between flex-wrap gap-3">
        <div>
          <h1 className="text-xl font-bold text-foreground">School Year & Semester</h1>
          <p className="text-sm text-muted-foreground">Manage academic periods, semester transitions, and week count</p>
        </div>
        <button
          onClick={openAdd}
          className="flex items-center gap-1.5 px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors"
        >
          <Plus size={14} /> Add Period
        </button>
      </div>

      {/* Active period banner */}
      {active && (
        <div className="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-4 py-3 flex-wrap gap-2">
          <div className="flex items-center gap-2">
            <span className="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse flex-shrink-0" />
            <span className="text-sm font-medium text-green-800">
              Active period: <strong>{active.label} – {active.schoolYear}</strong>
            </span>
          </div>
          <div className="flex items-center gap-3 text-xs text-green-700">
            <span className="flex items-center gap-1"><Clock size={11} /> {active.startDate} – {active.endDate}</span>
            <span className="flex items-center gap-1"><Hash size={11} /> {active.totalWeeks} academic weeks</span>
          </div>
        </div>
      )}

      {/* Semester list */}
      <div className="space-y-3">
        {semesters.map((s) => (
          <div
            key={s.id}
            className={`bg-card border rounded-xl px-5 py-4 transition-all ${
              s.isActive ? "border-primary/40 shadow-sm ring-1 ring-primary/20" : "border-card-border"
            }`}
          >
            <div className="flex items-start gap-4 flex-wrap">
              {/* Info */}
              <div className="flex-1 min-w-0">
                <div className="flex items-center gap-2 mb-1 flex-wrap">
                  <span className="text-sm font-bold text-foreground">{s.schoolYear} – {s.label}</span>
                  {s.isActive && (
                    <Badge color="green">● Active</Badge>
                  )}
                </div>
                <div className="flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                  <span className="flex items-center gap-1"><Calendar size={11} /> {s.startDate} – {s.endDate}</span>
                  <span className="flex items-center gap-1"><Hash size={11} /> <strong className="text-foreground">{s.totalWeeks}</strong> academic weeks</span>
                </div>

                {/* Week pills preview */}
                <div className="flex flex-wrap gap-1 mt-2">
                  {Array.from({ length: s.totalWeeks }, (_, i) => (
                    <span key={i} className={`text-[9px] px-1.5 py-0.5 rounded font-medium ${
                      s.isActive ? "bg-primary/10 text-primary" : "bg-muted text-muted-foreground"
                    }`}>
                      W{i + 1}
                    </span>
                  ))}
                </div>
              </div>

              {/* Actions */}
              <div className="flex items-center gap-2 flex-shrink-0">
                {!s.isActive && (
                  <button
                    onClick={() => handleSetActive(s.id)}
                    className="flex items-center gap-1 px-2.5 py-1.5 border border-border rounded-lg text-xs text-muted-foreground hover:bg-muted transition-colors"
                  >
                    <CheckCircle size={11} /> Set active
                  </button>
                )}
                <button
                  onClick={() => openEdit(s)}
                  className="flex items-center gap-1 px-2.5 py-1.5 border border-primary/30 text-primary bg-primary/5 rounded-lg text-xs hover:bg-primary/10 transition-colors font-medium"
                >
                  <Pencil size={11} /> Edit
                </button>
                {!s.isActive && (
                  <button
                    onClick={() => handleDelete(s.id)}
                    className="p-1.5 border border-red-200 text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                  >
                    <Trash2 size={13} />
                  </button>
                )}
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

// ── Year & Sections page ──────────────────────────────────────────────────────
function YearSections() {
  const [programs, setPrograms] = useState<Program[]>(PROGRAMS);
  const [showModal, setShowModal] = useState(false);
  const YEARS = [1, 2, 3, 4];

  return (
    <div className="space-y-5">
      {/* Header */}
      <div className="flex items-center justify-between flex-wrap gap-3">
        <div>
          <h1 className="text-xl font-bold text-foreground">Year & Sections</h1>
          <p className="text-sm text-muted-foreground">Configure section counts by department, program, and year level</p>
        </div>
        <button
          onClick={() => setShowModal(true)}
          className="flex items-center gap-1.5 px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors"
        >
          <Plus size={14} /> Add Config
        </button>
      </div>

      {/* Year Level Configuration card */}
      <div className="bg-card border border-card-border rounded-xl overflow-hidden">
        <div className="flex items-center gap-2 px-5 py-3 border-b border-border bg-muted/30">
          <Settings2 size={14} className="text-primary" />
          <span className="text-sm font-semibold text-foreground">Year Level Configuration</span>
        </div>
        <div className="p-5">
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
            {YEARS.map((y) => {
              const suffix = y === 1 ? "st" : y === 2 ? "nd" : y === 3 ? "rd" : "th";
              const total = programs.reduce((sum, p) => sum + (p.sectionsByYear[y] ?? 0), 0);
              return (
                <div key={y} className={`rounded-xl border p-4 text-center ${y === 1 ? "border-primary/30 bg-primary/5" : "border-border bg-muted/30"}`}>
                  <p className="text-2xl font-bold text-foreground">{total}</p>
                  <p className="text-xs text-muted-foreground mt-0.5">{y}{suffix} Year sections</p>
                  <div className="mt-2 flex flex-wrap gap-1 justify-center">
                    {programs.filter((p) => p.sectionsByYear[y]).map((p) => (
                      <span key={p.id} className={`text-[9px] px-1 py-0.5 rounded ${p.color} text-white font-bold`}>{p.code}</span>
                    ))}
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      </div>

      {/* Programs overview table */}
      <div className="bg-card border border-card-border rounded-xl overflow-hidden">
        <div className="flex items-center gap-2 px-5 py-3 border-b border-border bg-muted/30">
          <GraduationCap size={14} className="text-primary" />
          <span className="text-sm font-semibold text-foreground">All Programs Overview</span>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-border bg-slate-800 text-white">
                <th className="text-left text-xs font-semibold px-4 py-3 w-8">#</th>
                <th className="text-left text-xs font-semibold px-4 py-3 w-20">Code</th>
                <th className="text-left text-xs font-semibold px-4 py-3">Program Name</th>
                {YEARS.map((y) => {
                  const suffix = y === 1 ? "st" : y === 2 ? "nd" : y === 3 ? "rd" : "th";
                  return <th key={y} className="text-center text-xs font-semibold px-4 py-3">{y}{suffix} Year</th>;
                })}
                <th className="px-3 py-3 w-8" />
              </tr>
            </thead>
            <tbody>
              {programs.map((p, idx) => (
                <tr key={p.id} className="border-b border-border last:border-0 hover:bg-muted/20 transition-colors">
                  <td className="px-4 py-3 text-sm text-muted-foreground">{idx + 1}</td>
                  <td className="px-4 py-3">
                    <span className={`text-[11px] px-2 py-0.5 rounded font-bold text-white ${p.color}`}>{p.code}</span>
                  </td>
                  <td className="px-4 py-3 font-medium text-foreground">{p.name}</td>
                  {YEARS.map((y) => {
                    const count = p.sectionsByYear[y];
                    return (
                      <td key={y} className="px-4 py-3 text-center">
                        {count ? (
                          <span className="inline-flex items-center justify-center w-7 h-7 rounded-full bg-teal-100 text-teal-700 text-xs font-bold">
                            {count}
                          </span>
                        ) : (
                          <span className="text-muted-foreground">–</span>
                        )}
                      </td>
                    );
                  })}
                  <td className="px-3 py-3">
                    <button className="p-1 rounded hover:bg-muted transition-colors text-muted-foreground">
                      <MoreVertical size={14} />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Departments quick view */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
        {DEPARTMENTS.map((d) => (
          <div key={d.id} className="bg-card border border-card-border rounded-xl p-4">
            <div className="flex items-start justify-between mb-2">
              <Badge color="teal">{d.code}</Badge>
              <button className="p-1 rounded hover:bg-muted transition-colors text-muted-foreground">
                <Pencil size={11} />
              </button>
            </div>
            <p className="text-xs font-semibold text-foreground leading-snug">{d.name}</p>
            <p className="text-[10px] text-muted-foreground mt-1">{d.programIds.length} program{d.programIds.length !== 1 ? "s" : ""}</p>
          </div>
        ))}
      </div>
    </div>
  );
}

// ── Merged Admin Panel ────────────────────────────────────────────────────────
export default function AdminPanel() {
  const [, setLocation] = useLocation();
  const [page, setPage] = useState<AdminPage>("semester-settings");
  const [dark, setDark] = useState(false);

  const handleDark = () => {
    setDark((d) => !d);
    document.documentElement.classList.toggle("dark");
  };

  const NAV_ITEMS: {
    group: string;
    items: { id: AdminPage; label: string; icon: typeof LayoutDashboard }[];
  }[] = [
    {
      group: "Overview",
      items: [{ id: "dashboard", label: "Dashboard", icon: LayoutDashboard }],
    },
    {
      group: "Accounts",
      items: [{ id: "accounts", label: "Account Management", icon: Users }],
    },
    {
      group: "Academic Structure",
      items: [
        { id: "departments",    label: "Departments",    icon: Building2 },
        { id: "programs",       label: "Programs",       icon: GraduationCap },
        { id: "year-sections",  label: "Year & Sections", icon: Grid3x3 },
      ],
    },
    {
      group: "Settings",
      items: [
        { id: "semester-settings", label: "Semester Settings", icon: CalendarRange },
        { id: "calendar",          label: "Calendar",          icon: Calendar },
      ],
    },
  ];

  const activeSem = SEMESTERS.find((s) => s.isActive);
  const breadcrumb: Record<AdminPage, string> = {
    dashboard: "Dashboard",
    accounts: "Account Management",
    departments: "Departments",
    programs: "Programs",
    "year-sections": "Year & Sections",
    "semester-settings": "Semester Settings",
    calendar: "Calendar",
  };

  return (
    <div className="min-h-screen bg-background flex">
      {/* ── Left sidebar ── */}
      <aside className="w-56 flex-shrink-0 bg-card border-r border-border flex flex-col sticky top-0 h-screen overflow-y-auto">
        {/* Logo */}
        <div className="flex items-center gap-2.5 px-5 py-4 border-b border-border">
          <div className="w-7 h-7 rounded bg-primary flex items-center justify-center">
            <BookOpen size={14} className="text-white" />
          </div>
          <span className="font-bold text-base tracking-tight text-foreground">TERELEARN</span>
        </div>

        {/* Admin user block */}
        <div className="flex items-center gap-2.5 px-4 py-3 border-b border-border">
          <div className="w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center text-xs font-bold flex-shrink-0">SA</div>
          <div className="min-w-0">
            <div className="flex items-center gap-1.5">
              <p className="text-xs font-semibold text-foreground truncate">Super Admin</p>
              <span className="text-[9px] bg-amber-100 text-amber-700 px-1 py-0.5 rounded font-bold border border-amber-200">ADMIN</span>
            </div>
            <p className="text-[10px] text-muted-foreground">Administrator</p>
          </div>
        </div>

        {/* Nav */}
        <nav className="flex-1 px-2 py-3 space-y-0.5">
          {NAV_ITEMS.map((group) => (
            <div key={group.group} className="mb-2">
              <p className="px-3 pt-1 pb-1 text-[9px] font-bold text-muted-foreground uppercase tracking-wider">{group.group}</p>
              {group.items.map((item) => {
                const Icon = item.icon;
                const active = page === item.id;
                return (
                  <button
                    key={item.id}
                    onClick={() => setPage(item.id)}
                    className={`w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors ${
                      active
                        ? "bg-primary/10 text-primary"
                        : "text-muted-foreground hover:bg-muted hover:text-foreground"
                    }`}
                  >
                    <Icon size={14} className={active ? "text-primary" : ""} />
                    <span className="flex-1 text-left text-xs">{item.label}</span>
                    {active && <ChevronRight size={11} className="text-primary" />}
                  </button>
                );
              })}
            </div>
          ))}
        </nav>

        {/* Switch to faculty */}
        <div className="px-2 py-2">
          <button
            onClick={() => setLocation("/")}
            className="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs bg-primary/5 text-primary hover:bg-primary/10 transition-colors font-medium border border-primary/20"
          >
            <BookOpen size={12} /> Switch to Faculty View
          </button>
        </div>

        {/* Sign out */}
        <div className="px-2 py-2 border-t border-border">
          <button className="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs text-red-500 hover:bg-red-50 transition-colors font-medium">
            <LogOut size={12} /> Sign Out
          </button>
        </div>

        <div className="px-4 py-2 text-[9px] text-muted-foreground">Copyright © 2025–2026</div>
      </aside>

      {/* ── Main content ── */}
      <main className="flex-1 min-w-0 flex flex-col">
        {/* Top bar */}
        <div className="sticky top-0 z-30 bg-card border-b border-border px-6 py-3 flex items-center justify-between">
          {/* Breadcrumb */}
          <nav className="flex items-center gap-1.5 text-xs text-muted-foreground">
            <span className="hover:text-foreground cursor-pointer" onClick={() => setPage("dashboard")}>Dashboard</span>
            <ChevronRight size={11} />
            <span className="text-foreground font-medium">{breadcrumb[page]}</span>
          </nav>
          <div className="flex items-center gap-2">
            <button onClick={handleDark} className="p-2 rounded-lg hover:bg-muted transition-colors">
              {dark ? <Sun size={15} className="text-muted-foreground" /> : <Moon size={15} className="text-muted-foreground" />}
            </button>
            <button className="p-2 rounded-lg hover:bg-muted transition-colors relative">
              <Bell size={15} className="text-muted-foreground" />
            </button>
            <div className="w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center text-xs font-bold">SA</div>
          </div>
        </div>

        {/* Active semester badge */}
        {activeSem && (
          <div className="bg-primary/5 border-b border-primary/10 px-6 py-2 flex items-center justify-between">
            <p className="text-xs text-muted-foreground">
              Active semester: <strong className="text-primary">{activeSem.label} – {activeSem.schoolYear}</strong>
              <span className="mx-2">·</span>
              <strong className="text-foreground">{activeSem.totalWeeks} academic weeks</strong> (Week 1 – Week {activeSem.totalWeeks})
            </p>
            <span className="text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium border border-green-200 flex items-center gap-1">
              <span className="w-1.5 h-1.5 rounded-full bg-green-500 inline-block" /> {activeSem.label} · {activeSem.schoolYear}
            </span>
          </div>
        )}

        <div className="p-6 flex-1">
          {/* ── Page content ── */}
          {page === "semester-settings" && <SemesterSettings />}
          {page === "year-sections"     && <YearSections />}

          {/* Placeholder pages */}
          {["dashboard", "accounts", "departments", "programs", "calendar"].includes(page) && (
            <div className="flex flex-col items-center justify-center min-h-[400px] text-center gap-3">
              <div className="w-16 h-16 rounded-full bg-muted flex items-center justify-center">
                <LayoutDashboard size={24} className="text-muted-foreground" />
              </div>
              <p className="text-lg font-semibold text-foreground">{breadcrumb[page]}</p>
              <p className="text-sm text-muted-foreground max-w-sm">This section is coming soon. Navigate to <strong>Semester Settings</strong> or <strong>Year & Sections</strong> to see the full experience.</p>
              <div className="flex gap-2 mt-2">
                <button onClick={() => setPage("semester-settings")} className="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors">
                  Semester Settings
                </button>
                <button onClick={() => setPage("year-sections")} className="px-4 py-2 border border-border text-foreground rounded-lg text-sm hover:bg-muted transition-colors">
                  Year & Sections
                </button>
              </div>
            </div>
          )}
        </div>
      </main>
    </div>
  );
}
