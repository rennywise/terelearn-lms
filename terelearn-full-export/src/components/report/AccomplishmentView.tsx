import { useState, useRef, useEffect, useCallback } from "react";
import {
  FileText, Printer, Save, CheckCircle, ChevronRight, ChevronLeft,
  Hash, Calendar, Clock, Users, Upload, X, Sparkles, BookOpen,
  BarChart3, PenLine, Eye, RefreshCw, Image as ImageIcon,
} from "lucide-react";
import {
  SESSIONS, STUDENTS, ATTENDANCE, computeKMeans,
  getSessionAttendancePct, ClassData,
} from "@/data/mock";
import { getActiveSemester, getWeekOptions } from "@/data/admin-mock";
import { format, parseISO } from "date-fns";

// ── Confetti burst (pure CSS, no library) ─────────────────────────────────────
function Confetti({ active }: { active: boolean }) {
  const colors = ["#1a6b3c","#22c55e","#3b82f6","#f59e0b","#ec4899","#8b5cf6"];
  if (!active) return null;
  return (
    <div className="pointer-events-none fixed inset-0 z-[100] overflow-hidden">
      {Array.from({ length: 48 }, (_, i) => {
        const color = colors[i % colors.length];
        const left = `${Math.random() * 100}%`;
        const delay = `${Math.random() * 0.6}s`;
        const size = 6 + Math.random() * 8;
        return (
          <div
            key={i}
            style={{ left, top: "-10px", backgroundColor: color, width: size, height: size, animationDelay: delay }}
            className="absolute rounded-full animate-[confettiFall_1.4s_ease-in_forwards]"
          />
        );
      })}
    </div>
  );
}

// ── Session attendance mini bar ────────────────────────────────────────────────
function AttBar({ sessionId, total }: { sessionId: number; total: number }) {
  const present = ATTENDANCE.filter((a) => a.sessionId === sessionId && (a.status === "present" || a.status === "late")).length;
  const absent  = ATTENDANCE.filter((a) => a.sessionId === sessionId && a.status === "absent").length;
  const pct     = total > 0 ? Math.round((present / total) * 100) : 0;
  return (
    <div className="space-y-1 flex-1">
      <div className="flex items-center justify-between text-[9px] text-muted-foreground">
        <span className="text-green-600 font-medium">{present} present</span>
        <span className="font-bold text-foreground">{pct}%</span>
        <span className="text-red-500 font-medium">{absent} absent</span>
      </div>
      <div className="h-1.5 rounded-full bg-muted overflow-hidden">
        <div className="h-full rounded-full bg-green-500 transition-all" style={{ width: `${pct}%` }} />
      </div>
    </div>
  );
}

// ── Step indicator ─────────────────────────────────────────────────────────────
const STEPS = [
  { n: 1, label: "Session",    icon: Calendar },
  { n: 2, label: "Report",     icon: BookOpen },
  { n: 3, label: "Activities", icon: BarChart3 },
  { n: 4, label: "Approval",   icon: PenLine },
];

function StepBar({ step }: { step: number }) {
  const pct = Math.round((step / STEPS.length) * 100);
  return (
    <div className="space-y-2">
      <div className="flex items-center justify-between text-[10px]">
        <span className="text-muted-foreground font-medium">Step {step} of {STEPS.length}</span>
        <span className="font-bold text-primary">{pct}%</span>
      </div>
      {/* Track */}
      <div className="relative h-1.5 rounded-full bg-muted overflow-hidden">
        <div
          className="absolute left-0 top-0 h-full rounded-full bg-gradient-to-r from-primary to-teal-400 transition-all duration-500 ease-out"
          style={{ width: `${pct}%` }}
        />
      </div>
      {/* Steps */}
      <div className="flex items-center justify-between">
        {STEPS.map((s) => {
          const Icon = s.icon;
          const done = s.n < step;
          const active = s.n === step;
          return (
            <div key={s.n} className="flex flex-col items-center gap-1 flex-1">
              <div className={`w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 ${
                done   ? "bg-primary text-white shadow-sm shadow-primary/30" :
                active ? "bg-primary/10 text-primary border-2 border-primary" :
                         "bg-muted text-muted-foreground"
              }`}>
                {done ? <CheckCircle size={13} /> : <Icon size={12} />}
              </div>
              <span className={`text-[9px] font-medium ${active ? "text-primary" : "text-muted-foreground"}`}>{s.label}</span>
            </div>
          );
        })}
      </div>
    </div>
  );
}

// ── Char-counted textarea ─────────────────────────────────────────────────────
function CountedTextarea({
  label, value, onChange, rows = 3, placeholder = "", max = 500,
}: { label: string; value: string; onChange: (v: string) => void; rows?: number; placeholder?: string; max?: number }) {
  const pct = (value.length / max) * 100;
  return (
    <div>
      <div className="flex items-center justify-between mb-1">
        <label className="text-xs font-semibold text-foreground">{label}</label>
        <span className={`text-[10px] font-medium ${pct > 90 ? "text-red-500" : "text-muted-foreground"}`}>
          {value.length}/{max}
        </span>
      </div>
      <textarea
        value={value}
        onChange={(e) => onChange(e.target.value.slice(0, max))}
        rows={rows}
        placeholder={placeholder}
        className="w-full text-sm border border-input rounded-xl px-3 py-2.5 bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 resize-none placeholder:text-muted-foreground transition-all"
      />
      {pct > 0 && (
        <div className="h-0.5 rounded-full bg-muted mt-1 overflow-hidden">
          <div className={`h-full rounded-full transition-all ${pct > 90 ? "bg-red-400" : "bg-primary"}`} style={{ width: `${pct}%` }} />
        </div>
      )}
    </div>
  );
}

// ── Smart field ───────────────────────────────────────────────────────────────
function Field({
  label, value, onChange, span = 1, placeholder = "", type = "text", hint = "",
}: { label: string; value: string; onChange: (v: string) => void; span?: number; placeholder?: string; type?: string; hint?: string }) {
  return (
    <div className={span === 2 ? "col-span-2" : ""}>
      <label className="block text-xs font-semibold text-foreground mb-1">{label}</label>
      <input
        type={type}
        value={value}
        onChange={(e) => onChange(e.target.value)}
        placeholder={placeholder}
        className="w-full text-sm border border-input rounded-xl px-3 py-2.5 bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 placeholder:text-muted-foreground transition-all"
      />
      {hint && <p className="text-[10px] text-muted-foreground mt-0.5">{hint}</p>}
    </div>
  );
}

// ── Photo drop zone ───────────────────────────────────────────────────────────
function PhotoZone({ photos, onAdd, onRemove }: {
  photos: string[];
  onAdd: (url: string) => void;
  onRemove: (idx: number) => void;
}) {
  const [dragging, setDragging] = useState(false);
  const ref = useRef<HTMLInputElement>(null);

  const handleFiles = (files: FileList | null) => {
    if (!files) return;
    Array.from(files).forEach((f) => {
      const reader = new FileReader();
      reader.onload = (e) => onAdd(e.target?.result as string);
      reader.readAsDataURL(f);
    });
  };

  return (
    <div>
      <label className="text-xs font-semibold text-foreground block mb-1.5">Photo Documentation</label>
      <div
        className={`border-2 border-dashed rounded-2xl p-5 text-center transition-all cursor-pointer ${
          dragging ? "border-primary bg-primary/5 scale-[1.01]" : "border-border hover:border-primary/50 hover:bg-muted/30"
        }`}
        onDragOver={(e) => { e.preventDefault(); setDragging(true); }}
        onDragLeave={() => setDragging(false)}
        onDrop={(e) => { e.preventDefault(); setDragging(false); handleFiles(e.dataTransfer.files); }}
        onClick={() => ref.current?.click()}
      >
        <input ref={ref} type="file" accept="image/*" multiple className="hidden" onChange={(e) => handleFiles(e.target.files)} />
        {photos.length === 0 ? (
          <div className="flex flex-col items-center gap-2 py-2">
            <div className="w-10 h-10 rounded-full bg-muted flex items-center justify-center">
              <Upload size={18} className="text-muted-foreground" />
            </div>
            <p className="text-sm text-muted-foreground font-medium">Drop class photos here</p>
            <p className="text-[10px] text-muted-foreground">or click to browse · PNG, JPG up to 10MB</p>
            <p className="text-[9px] text-muted-foreground bg-muted px-2 py-0.5 rounded-full">Shown on Page 2 of the print preview</p>
          </div>
        ) : (
          <div className="flex flex-wrap gap-2 justify-center" onClick={(e) => e.stopPropagation()}>
            {photos.map((src, i) => (
              <div key={i} className="relative group">
                <img src={src} alt="" className="w-20 h-20 object-cover rounded-xl border border-border" />
                <button
                  onClick={() => onRemove(i)}
                  className="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-red-500 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                >
                  <X size={10} />
                </button>
              </div>
            ))}
            <div
              className="w-20 h-20 rounded-xl border-2 border-dashed border-border flex items-center justify-center hover:border-primary/40 transition-colors cursor-pointer"
              onClick={() => ref.current?.click()}
            >
              <Plus size={18} className="text-muted-foreground" />
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
function Plus({ size, className }: { size: number; className?: string }) {
  return <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} className={className}><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>;
}

// ── Live document preview (right pane) ────────────────────────────────────────
function LivePreview({ f, cls, step }: { f: FormState; cls: ClassData; step: number }) {
  const sessions = SESSIONS.filter((s) => s.classId === cls.id);
  const firstSess = sessions[0];
  const present = ATTENDANCE.filter((a) => a.sessionId === (firstSess?.id ?? 0) && (a.status === "present" || a.status === "late"));
  const absent  = ATTENDANCE.filter((a) => a.sessionId === (firstSess?.id ?? 0) && a.status === "absent");
  const presentStudents = present.map((a) => STUDENTS.find((s) => s.id === a.studentId)?.name ?? "");
  const absentStudents  = absent.map((a) => STUDENTS.find((s) => s.id === a.studentId)?.name ?? "");
  const maxRows = Math.max(presentStudents.length, absentStudents.length, 8);

  // highlight which step's fields are active
  const highlight = (field: "header" | "activities" | "approval" | "attendance") => {
    const map: Record<typeof field, number[]> = {
      header:     [1, 2],
      activities: [3],
      approval:   [4],
      attendance: [1, 4],
    };
    return map[field].includes(step) ? "bg-yellow-50 transition-colors duration-300" : "";
  };

  return (
    <div className="bg-white text-black text-[8px] font-sans leading-snug h-full overflow-y-auto p-4 rounded-xl border border-border shadow-inner">
      {/* Header */}
      <div className={`text-center mb-3 p-2 rounded ${highlight("header")}`}>
        <p className="text-[7px] uppercase tracking-widest text-gray-500">Republic of the Philippines</p>
        <p className="text-[7px] uppercase tracking-widest text-gray-500">{f.institution || "State University"}</p>
        <p className="font-bold text-[10px] uppercase mt-1">Accomplishment Report</p>
        <p className="text-[7px] text-gray-500">{f.department}</p>
      </div>

      {/* Info table */}
      <div className={`rounded p-1 mb-2 ${highlight("header")}`}>
        <table className="w-full border-collapse border border-gray-300 text-[7px]">
          <tbody>
            <tr>
              <td className="border border-gray-300 px-1 py-0.5 font-semibold bg-gray-50 w-20">Academic Week</td>
              <td className="border border-gray-300 px-1 py-0.5 w-16">{f.academicWeek}</td>
              <td className="border border-gray-300 px-1 py-0.5 font-semibold bg-gray-50">Date Covered</td>
              <td className="border border-gray-300 px-1 py-0.5">{f.dateCovered}</td>
            </tr>
            <tr>
              <td className="border border-gray-300 px-1 py-0.5 font-semibold bg-gray-50">Name</td>
              <td className="border border-gray-300 px-1 py-0.5" colSpan={3}>{f.instructorName}</td>
            </tr>
            <tr>
              <td className="border border-gray-300 px-1 py-0.5 font-semibold bg-gray-50">Subject</td>
              <td className="border border-gray-300 px-1 py-0.5">{f.subject}</td>
              <td className="border border-gray-300 px-1 py-0.5 font-semibold bg-gray-50">Units</td>
              <td className="border border-gray-300 px-1 py-0.5">{f.unit}</td>
            </tr>
            <tr>
              <td className="border border-gray-300 px-1 py-0.5 font-semibold bg-gray-50">Section</td>
              <td className="border border-gray-300 px-1 py-0.5">{f.section}</td>
              <td className="border border-gray-300 px-1 py-0.5 font-semibold bg-gray-50">Status</td>
              <td className="border border-gray-300 px-1 py-0.5">{f.status}</td>
            </tr>
            <tr>
              <td className="border border-gray-300 px-1 py-0.5 font-semibold bg-gray-50">Class Size</td>
              <td className="border border-gray-300 px-1 py-0.5" colSpan={3}>{f.classSize}</td>
            </tr>
          </tbody>
        </table>
      </div>

      {/* Activities */}
      <div className={`rounded p-1 mb-2 ${highlight("activities")}`}>
        <table className="w-full border-collapse border border-gray-300 text-[7px]">
          <tbody>
            <tr><td className="border border-gray-300 px-1 py-0.5 font-bold text-center bg-gray-100" colSpan={4}>ACTIVITIES</td></tr>
            <tr><td className="border border-gray-300 px-1 py-0.5 font-semibold bg-gray-50" colSpan={4}>Synchronous</td></tr>
            <tr>
              <td className="border border-gray-300 px-1 py-0.5 font-semibold">Attendees</td>
              <td className="border border-gray-300 px-1 py-0.5">{f.noAttendees}</td>
              <td className="border border-gray-300 px-1 py-0.5 font-semibold">Absent</td>
              <td className="border border-gray-300 px-1 py-0.5">{f.noAbsent}</td>
            </tr>
            <tr>
              <td className="border border-gray-300 px-1 py-0.5 font-semibold align-top">Topics:</td>
              <td className="border border-gray-300 px-1 py-0.5 whitespace-pre-line" colSpan={3} style={{ maxHeight: 40, overflow: "hidden" }}>
                {f.topicsCovered.slice(0, 120)}{f.topicsCovered.length > 120 ? "…" : ""}
              </td>
            </tr>
            <tr>
              <td className="border border-gray-300 px-1 py-0.5 font-semibold">Duration</td>
              <td className="border border-gray-300 px-1 py-0.5" colSpan={3}>{f.duration}</td>
            </tr>
            <tr>
              <td className="border border-gray-300 px-1 py-0.5 font-semibold align-top">Activities:</td>
              <td className="border border-gray-300 px-1 py-0.5 whitespace-pre-line" colSpan={3}>
                {f.activitiesConducted.slice(0, 80)}{f.activitiesConducted.length > 80 ? "…" : ""}
              </td>
            </tr>
            <tr><td className="border border-gray-300 px-1 py-0.5 font-semibold bg-gray-50" colSpan={4}>Asynchronous</td></tr>
            <tr>
              <td className="border border-gray-300 px-1 py-0.5 text-gray-400 italic" colSpan={4} style={{ height: 18 }}>
                {f.asyncActivities || "(none specified)"}
              </td>
            </tr>
            <tr><td className="border border-gray-300 px-1 py-0.5 font-semibold bg-gray-50" colSpan={4}>Laboratory</td></tr>
            <tr>
              <td className="border border-gray-300 px-1 py-0.5 text-gray-400 italic" colSpan={4} style={{ height: 18 }}>
                {f.labActivities || "(none specified)"}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      {/* Approval */}
      <div className={`rounded p-1 mb-2 ${highlight("approval")}`}>
        <table className="w-full border-collapse border border-gray-300 text-[7px]">
          <tbody>
            <tr>
              <td className="border border-gray-300 px-1 py-0.5 font-semibold bg-gray-100">FOR COLLEGE DEPARTMENT</td>
              <td className="border border-gray-300 px-1 py-0.5 font-semibold bg-gray-100">FOR HRD</td>
            </tr>
            <tr>
              <td className="border border-gray-300 px-2 py-2" style={{ height: 24 }}>
                <div className="flex flex-col gap-0.5">
                  <span className="font-semibold">{f.deanName || "Dean / Dept. Head"}</span>
                  <span className="text-gray-400">Dean · {f.dateSubmitted || "date signed"}</span>
                </div>
              </td>
              <td className="border border-gray-300 px-2 py-2">
                <span className="text-gray-400">Received: {f.hrdDate || "___"}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      {/* Attendance */}
      <div className={`rounded p-1 ${highlight("attendance")}`}>
        <table className="w-full border-collapse border border-gray-300 text-[7px]">
          <tbody>
            <tr><td className="border border-gray-300 px-1 py-0.5 font-bold text-center bg-gray-100" colSpan={4}>ATTENDANCE REPORT</td></tr>
            <tr>
              <td className="border border-gray-300 px-1 py-0.5 font-semibold text-center w-4">#</td>
              <td className="border border-gray-300 px-1 py-0.5 font-semibold">PRESENT</td>
              <td className="border border-gray-300 px-1 py-0.5 font-semibold text-center w-4">#</td>
              <td className="border border-gray-300 px-1 py-0.5 font-semibold">ABSENT</td>
            </tr>
            {Array.from({ length: Math.min(maxRows, 6) }, (_, i) => (
              <tr key={i}>
                <td className="border border-gray-300 px-1 py-0.5 text-center text-gray-400">{i + 1}</td>
                <td className="border border-gray-300 px-1 py-0.5">{presentStudents[i] ?? ""}</td>
                <td className="border border-gray-300 px-1 py-0.5 text-center text-gray-400">{i + 1}</td>
                <td className="border border-gray-300 px-1 py-0.5">{absentStudents[i] ?? ""}</td>
              </tr>
            ))}
            {maxRows > 6 && (
              <tr><td colSpan={4} className="border border-gray-300 px-1 py-0.5 text-center text-gray-400 italic">+{maxRows - 6} more rows in full print</td></tr>
            )}
          </tbody>
        </table>
      </div>

      {/* Photo placeholder */}
      {f.photos.length > 0 ? (
        <div className="mt-2 grid grid-cols-3 gap-1">
          {f.photos.slice(0, 3).map((src, i) => (
            <img key={i} src={src} alt="" className="w-full h-12 object-cover rounded border border-gray-300" />
          ))}
        </div>
      ) : (
        <div className="mt-2 border border-gray-300 rounded p-2 text-center text-gray-400 italic text-[7px]">Photo documentation area</div>
      )}
    </div>
  );
}

// ── Full print preview modal ───────────────────────────────────────────────────
function PrintModal({ f, cls, onClose }: { f: FormState; cls: ClassData; onClose: () => void }) {
  const sessions = SESSIONS.filter((s) => s.classId === cls.id);
  const firstSess = sessions[0];
  const present = ATTENDANCE.filter((a) => a.sessionId === (firstSess?.id ?? 0) && (a.status === "present" || a.status === "late"));
  const absent  = ATTENDANCE.filter((a) => a.sessionId === (firstSess?.id ?? 0) && a.status === "absent");
  const presentStudents = present.map((a) => STUDENTS.find((s) => s.id === a.studentId)?.name ?? "");
  const absentStudents  = absent.map((a) => STUDENTS.find((s) => s.id === a.studentId)?.name ?? "");
  const maxRows = Math.max(presentStudents.length, absentStudents.length, 12);

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
      <div className="bg-white text-black rounded-2xl shadow-2xl w-full max-w-2xl my-4">
        <div className="flex items-center justify-between px-5 py-3 border-b border-gray-200 bg-gray-50 rounded-t-2xl">
          <div>
            <p className="text-sm font-bold text-gray-800">Print Preview — Accomplishment Report</p>
            <p className="text-[10px] text-gray-500">{cls.name} · {f.section} · {f.academicWeek}</p>
          </div>
          <div className="flex gap-2">
            <button onClick={() => window.print()} className="flex items-center gap-1.5 px-3 py-1.5 bg-green-800 text-white rounded-lg text-xs font-semibold hover:bg-green-900">
              <Printer size={12} /> Print
            </button>
            <button onClick={onClose} className="px-3 py-1.5 border border-gray-300 text-gray-600 rounded-lg text-xs hover:bg-gray-100 font-medium">✕ Close</button>
          </div>
        </div>

        <div className="px-8 py-7 text-[10px] font-sans leading-tight space-y-3">
          {/* Title */}
          <div className="text-center">
            <p className="text-[8px] uppercase tracking-widest text-gray-500">Republic of the Philippines</p>
            <p className="text-[8px] uppercase tracking-widest text-gray-500">{f.institution}</p>
            <p className="font-bold text-sm uppercase mt-1">Accomplishment Report</p>
            <p className="text-[9px] text-gray-500">{f.department}</p>
          </div>

          <table className="w-full border-collapse border border-gray-400 text-[9px]">
            <tbody>
              <tr>
                <td className="border border-gray-400 px-2 py-1 font-semibold bg-gray-50 w-28">Academic Week</td>
                <td className="border border-gray-400 px-2 py-1">{f.academicWeek}</td>
                <td className="border border-gray-400 px-2 py-1 font-semibold bg-gray-50">Date Covered</td>
                <td className="border border-gray-400 px-2 py-1">{f.dateCovered}</td>
              </tr>
              <tr><td className="border border-gray-400 px-2 py-1 font-semibold bg-gray-50">Name</td><td colSpan={3} className="border border-gray-400 px-2 py-1">{f.instructorName}</td></tr>
              <tr>
                <td className="border border-gray-400 px-2 py-1 font-semibold bg-gray-50">Subject</td>
                <td className="border border-gray-400 px-2 py-1">{f.subject}</td>
                <td className="border border-gray-400 px-2 py-1 font-semibold bg-gray-50">Units</td>
                <td className="border border-gray-400 px-2 py-1">{f.unit}</td>
              </tr>
              <tr>
                <td className="border border-gray-400 px-2 py-1 font-semibold bg-gray-50">Section</td>
                <td className="border border-gray-400 px-2 py-1">{f.section}</td>
                <td className="border border-gray-400 px-2 py-1 font-semibold bg-gray-50">Status</td>
                <td className="border border-gray-400 px-2 py-1">{f.status}</td>
              </tr>
              <tr><td className="border border-gray-400 px-2 py-1 font-semibold bg-gray-50">Class Size</td><td colSpan={3} className="border border-gray-400 px-2 py-1">{f.classSize}</td></tr>
            </tbody>
          </table>

          <table className="w-full border-collapse border border-gray-400 text-[9px]">
            <tbody>
              <tr><td colSpan={4} className="border border-gray-400 px-2 py-1 font-bold text-center bg-gray-100">ACTIVITIES</td></tr>
              <tr><td colSpan={4} className="border border-gray-400 px-2 py-0.5 font-semibold bg-gray-50">Synchronous Activities</td></tr>
              <tr>
                <td className="border border-gray-400 px-2 py-1 font-semibold">Attendees</td><td className="border border-gray-400 px-2 py-1">{f.noAttendees}</td>
                <td className="border border-gray-400 px-2 py-1 font-semibold">Absent</td><td className="border border-gray-400 px-2 py-1">{f.noAbsent}</td>
              </tr>
              <tr>
                <td className="border border-gray-400 px-2 py-1 font-semibold align-top">Topics Covered:</td>
                <td className="border border-gray-400 px-2 py-1 whitespace-pre-line" colSpan={3}>{f.topicsCovered}</td>
              </tr>
              <tr>
                <td className="border border-gray-400 px-2 py-1 font-semibold">Date Conducted</td><td className="border border-gray-400 px-2 py-1">{f.dateConducted}</td>
                <td className="border border-gray-400 px-2 py-1 font-semibold">Time</td><td className="border border-gray-400 px-2 py-1">{f.timeConducted}</td>
              </tr>
              <tr>
                <td className="border border-gray-400 px-2 py-1 font-semibold align-top">Activities:</td>
                <td className="border border-gray-400 px-2 py-1 whitespace-pre-line" colSpan={3}>{f.activitiesConducted}</td>
              </tr>
              <tr><td className="border border-gray-400 px-2 py-1 font-semibold">Duration</td><td colSpan={3} className="border border-gray-400 px-2 py-1">{f.duration}</td></tr>
              <tr><td colSpan={4} className="border border-gray-400 px-2 py-0.5 font-semibold bg-gray-50">Asynchronous Activities</td></tr>
              <tr><td colSpan={4} className="border border-gray-400 px-2 py-1 italic text-gray-500 whitespace-pre-line" style={{ minHeight: 32 }}>{f.asyncActivities || "(none specified)"}</td></tr>
              <tr><td colSpan={4} className="border border-gray-400 px-2 py-0.5 font-semibold bg-gray-50">Laboratory Activities</td></tr>
              <tr><td colSpan={4} className="border border-gray-400 px-2 py-1 italic text-gray-500 whitespace-pre-line" style={{ minHeight: 32 }}>{f.labActivities || "(none specified)"}</td></tr>
            </tbody>
          </table>

          <table className="w-full border-collapse border border-gray-400 text-[9px]">
            <tbody>
              <tr><td className="border border-gray-400 px-2 py-0.5 font-semibold bg-gray-100">FOR COLLEGE DEPARTMENT</td><td className="border border-gray-400 px-2 py-0.5 font-semibold bg-gray-100">FOR HRD</td></tr>
              <tr>
                <td className="border border-gray-400 px-2 py-3">
                  <span className="block font-semibold">{f.deanName}</span>
                  <span className="text-gray-500">Dean · Date: {f.dateSubmitted}</span>
                </td>
                <td className="border border-gray-400 px-2 py-3">
                  <span className="text-gray-500">Date Received: {f.hrdDate}</span>
                </td>
              </tr>
            </tbody>
          </table>

          <table className="w-full border-collapse border border-gray-400 text-[9px]">
            <tbody>
              <tr><td colSpan={4} className="border border-gray-400 px-2 py-0.5 font-bold text-center bg-gray-100">ATTENDANCE REPORT</td></tr>
              <tr>
                <td className="border border-gray-400 px-2 py-0.5 font-semibold text-center w-6">#</td>
                <td className="border border-gray-400 px-2 py-0.5 font-semibold">PRESENT</td>
                <td className="border border-gray-400 px-2 py-0.5 font-semibold text-center w-6">#</td>
                <td className="border border-gray-400 px-2 py-0.5 font-semibold">ABSENT</td>
              </tr>
              {Array.from({ length: maxRows }, (_, i) => (
                <tr key={i}>
                  <td className="border border-gray-400 px-2 py-0.5 text-center text-gray-400">{i+1}</td>
                  <td className="border border-gray-400 px-2 py-0.5">{presentStudents[i] ?? ""}</td>
                  <td className="border border-gray-400 px-2 py-0.5 text-center text-gray-400">{i+1}</td>
                  <td className="border border-gray-400 px-2 py-0.5">{absentStudents[i] ?? ""}</td>
                </tr>
              ))}
            </tbody>
          </table>

          {f.photos.length > 0 && (
            <div>
              <p className="font-semibold mb-2 text-[9px]">PHOTO DOCUMENTATION:</p>
              <div className="grid grid-cols-3 gap-2">
                {f.photos.map((src, i) => <img key={i} src={src} alt="" className="w-full h-24 object-cover rounded border border-gray-300" />)}
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

// ── Form state ─────────────────────────────────────────────────────────────────
interface FormState {
  sessionId: number;
  institution: string;
  department: string;
  academicWeek: string;
  dateCovered: string;
  instructorName: string;
  subject: string;
  unit: string;
  section: string;
  status: string;
  classSize: string;
  noAttendees: string;
  noAbsent: string;
  topicsCovered: string;
  dateConducted: string;
  timeConducted: string;
  activitiesConducted: string;
  duration: string;
  asyncActivities: string;
  labActivities: string;
  deanName: string;
  dateSubmitted: string;
  hrdDate: string;
  photos: string[];
}

// ── Main component ─────────────────────────────────────────────────────────────
export default function AccomplishmentView({ classId, cls }: { classId: number; cls: ClassData }) {
  const sessions    = SESSIONS.filter((s) => s.classId === classId);
  const activeSem   = getActiveSemester();
  const weekOptions = getWeekOptions();
  const analytics   = computeKMeans();

  const firstSess = sessions[0];
  const lastSess  = sessions[sessions.length - 1];

  const presentCount = ATTENDANCE.filter(
    (a) => a.sessionId === (firstSess?.id ?? 0) && (a.status === "present" || a.status === "late")
  ).length;
  const absentCount = ATTENDANCE.filter(
    (a) => a.sessionId === (firstSess?.id ?? 0) && a.status === "absent"
  ).length;

  const defaultTopics = sessions
    .map((s, i) => `${i + 1}. ${format(parseISO(s.date), "MMM d, yyyy")}: ${s.topics}`)
    .join("\n");

  const [f, setFRaw] = useState<FormState>({
    sessionId:           firstSess?.id ?? 0,
    institution:         "University of Science and Technology",
    department:          "College of Information Technology and Computing",
    academicWeek:        weekOptions[3] ?? "4th Week",
    dateCovered:         firstSess && lastSess
      ? `${format(parseISO(firstSess.date), "MMMM d")}–${format(parseISO(lastSess.date), "d, yyyy")}`
      : "",
    instructorName:      "Mr. Harold Ramirez Lucero",
    subject:             cls.name,
    unit:                "3",
    section:             cls.section,
    status:              "Regular",
    classSize:           String(cls.studentCount),
    noAttendees:         String(presentCount),
    noAbsent:            String(absentCount),
    topicsCovered:       defaultTopics,
    dateConducted:       firstSess ? format(parseISO(firstSess.date), "MMMM d, yyyy") : "",
    timeConducted:       `${cls.timeStart} – ${cls.timeEnd}`,
    activitiesConducted: "PowerPoint Presentation / Discussion\nQ & A\nHands-on coding activity",
    duration:            "3 hrs.",
    asyncActivities:     "",
    labActivities:       "",
    deanName:            "Harold Ramirez Lucero",
    dateSubmitted:       "",
    hrdDate:             "",
    photos:              [],
  });

  const set = useCallback(<K extends keyof FormState>(key: K) => (val: FormState[K]) => {
    setFRaw((prev) => ({ ...prev, [key]: val }));
  }, []);

  const [step, setStep] = useState(1);
  const [saved, setSaved] = useState(false);
  const [showPrint, setShowPrint] = useState(false);
  const [confetti, setConfetti] = useState(false);
  const [showPreviewPane, setShowPreviewPane] = useState(true);

  const handleSave = () => { setSaved(true); setTimeout(() => setSaved(false), 2500); };

  const handlePrint = () => {
    setConfetti(true);
    setTimeout(() => setConfetti(false), 1600);
    setTimeout(() => setShowPrint(true), 400);
  };

  // Update session fields when session changes
  const handleSessionChange = (sessId: number) => {
    const sess = sessions.find((s) => s.id === sessId);
    if (!sess) return;
    const p = ATTENDANCE.filter((a) => a.sessionId === sessId && (a.status === "present" || a.status === "late")).length;
    const ab = ATTENDANCE.filter((a) => a.sessionId === sessId && a.status === "absent").length;
    setFRaw((prev) => ({
      ...prev,
      sessionId: sessId,
      noAttendees: String(p),
      noAbsent: String(ab),
      dateConducted: format(parseISO(sess.date), "MMMM d, yyyy"),
    }));
  };

  const avgAtt = sessions.length
    ? Math.round(sessions.map((s) => getSessionAttendancePct(s.id)).reduce((a, b) => a + b, 0) / sessions.length)
    : 0;

  return (
    <div className="flex flex-col lg:flex-row h-full">
      <Confetti active={confetti} />
      {showPrint && <PrintModal f={f} cls={cls} onClose={() => setShowPrint(false)} />}

      {/* ══ LEFT: Wizard pane ══ */}
      <div className={`flex flex-col overflow-y-auto w-full ${showPreviewPane ? "lg:w-[58%]" : "lg:flex-1"} transition-all duration-300`}>
        {/* Toolbar */}
        <div className="sticky top-0 z-10 bg-card/95 backdrop-blur-sm border-b border-border px-5 py-3">
          <div className="flex items-center justify-between mb-3 flex-wrap gap-2">
            <div>
              <div className="flex items-center gap-2">
                <div className="w-6 h-6 rounded-lg bg-primary/10 flex items-center justify-center">
                  <FileText size={13} className="text-primary" />
                </div>
                <h2 className="text-sm font-bold text-foreground">Accomplishment Report</h2>
              </div>
              {activeSem && (
                <p className="text-[10px] text-muted-foreground mt-0.5 ml-8">
                  {activeSem.label} {activeSem.schoolYear} · {activeSem.totalWeeks} weeks
                </p>
              )}
            </div>
            <div className="flex items-center gap-2">
              <button
                onClick={() => setShowPreviewPane((p) => !p)}
                className={`flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-medium border transition-all ${
                  showPreviewPane ? "bg-primary/10 text-primary border-primary/30" : "border-border text-muted-foreground hover:bg-muted"
                }`}
              >
                <Eye size={11} /> {showPreviewPane ? "Hide" : "Show"} Preview
              </button>
              <button
                onClick={handleSave}
                className={`flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-medium border transition-all ${
                  saved ? "bg-green-50 border-green-300 text-green-700" : "border-border text-muted-foreground hover:bg-muted"
                }`}
              >
                {saved ? <><CheckCircle size={11} /> Saved!</> : <><Save size={11} /> Save draft</>}
              </button>
            </div>
          </div>
          <StepBar step={step} />
        </div>

        {/* Stats banner */}
        <div className="mx-5 mt-4 bg-primary/5 border border-primary/20 rounded-xl px-4 py-3 flex items-center gap-3">
          <RefreshCw size={13} className="text-primary flex-shrink-0" />
          <p className="text-[11px] text-muted-foreground leading-relaxed">
            Auto-filled · <strong className="text-foreground">{sessions.length} sessions</strong> · avg attendance <strong className="text-green-600">{avgAtt}%</strong>
            {" "}· <strong className="text-green-600">{analytics.filter((c) => c.cluster === "high").length}</strong> high performers
            {" "}· <strong className="text-red-500">{analytics.filter((c) => c.cluster === "at_risk").length}</strong> at risk
          </p>
        </div>

        {/* ── Step content ── */}
        <div className="flex-1 px-5 py-4 space-y-4">

          {/* STEP 1 — Session & Class Details */}
          {step === 1 && (
            <div className="space-y-4 animate-in fade-in slide-in-from-right-4 duration-200">
              {/* Session selector as cards */}
              <div className="bg-card border border-card-border rounded-2xl p-5">
                <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-3 flex items-center gap-1.5">
                  <Calendar size={11} /> Session Selector
                </p>
                <p className="text-xs text-muted-foreground mb-3">Select the session to generate this report for:</p>
                <div className="space-y-2">
                  {sessions.length === 0 && (
                    <p className="text-xs text-muted-foreground text-center py-6">No sessions logged yet — visit the Attendance tab first.</p>
                  )}
                  {sessions.map((s) => {
                    const selected = f.sessionId === s.id;
                    const p = ATTENDANCE.filter((a) => a.sessionId === s.id && (a.status === "present" || a.status === "late")).length;
                    const ab = ATTENDANCE.filter((a) => a.sessionId === s.id && a.status === "absent").length;
                    const pct = cls.studentCount > 0 ? Math.round((p / cls.studentCount) * 100) : 0;
                    return (
                      <button
                        key={s.id}
                        onClick={() => handleSessionChange(s.id)}
                        className={`w-full text-left flex items-center gap-3 px-4 py-3 rounded-xl border-2 transition-all ${
                          selected ? "border-primary bg-primary/5 shadow-sm" : "border-border hover:border-primary/30 hover:bg-muted/30"
                        }`}
                      >
                        <div className={`w-5 h-5 rounded-full flex-shrink-0 border-2 flex items-center justify-center transition-all ${
                          selected ? "border-primary bg-primary" : "border-muted-foreground"
                        }`}>
                          {selected && <div className="w-2 h-2 bg-white rounded-full" />}
                        </div>
                        <div className="flex-1 min-w-0">
                          <div className="flex items-center justify-between mb-1.5">
                            <span className={`text-sm font-semibold ${selected ? "text-primary" : "text-foreground"}`}>
                              {format(parseISO(s.date), "MMMM d, yyyy")}
                            </span>
                            <span className={`text-[10px] px-2 py-0.5 rounded-full font-bold ${pct >= 75 ? "bg-green-100 text-green-700" : pct >= 50 ? "bg-amber-100 text-amber-700" : "bg-red-100 text-red-600"}`}>
                              {pct}% present
                            </span>
                          </div>
                          <AttBar sessionId={s.id} total={cls.studentCount} />
                          <p className="text-[10px] text-muted-foreground mt-1 truncate">{s.topics}</p>
                        </div>
                      </button>
                    );
                  })}
                </div>
                {/* Selected session summary */}
                {f.sessionId > 0 && (
                  <div className="mt-3 bg-muted/40 rounded-xl px-3 py-2 text-[10px] text-muted-foreground">
                    ✓ Session selected · {f.noAttendees} attendees · {f.noAbsent} absent · {f.dateConducted}
                  </div>
                )}
              </div>

              {/* Class details */}
              <div className="bg-card border border-card-border rounded-2xl p-5">
                <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-4 flex items-center gap-1.5">
                  <BookOpen size={11} /> Class Details
                </p>
                <div className="grid grid-cols-2 gap-3">
                  <Field label="Institution" value={f.institution} onChange={set("institution")} span={2} />
                  <Field label="Department / College" value={f.department} onChange={set("department")} span={2} />
                  <Field label="Instructor Name" value={f.instructorName} onChange={set("instructorName")} span={2} />
                  <Field label="Subject" value={f.subject} onChange={set("subject")} />
                  <Field label="Section" value={f.section} onChange={set("section")} />
                  <Field label="Employment Status" value={f.status} onChange={set("status")} placeholder="Regular / Part time" />
                  <Field label="Class Size" value={f.classSize} onChange={set("classSize")} />
                </div>
              </div>
            </div>
          )}

          {/* STEP 2 — Report Details */}
          {step === 2 && (
            <div className="space-y-4 animate-in fade-in slide-in-from-right-4 duration-200">
              <div className="bg-card border border-card-border rounded-2xl p-5">
                <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-4 flex items-center gap-1.5">
                  <Hash size={11} className="text-primary" /> Academic Week
                </p>
                {/* Week pill grid */}
                <label className="text-xs font-semibold text-foreground mb-2 block">
                  Select week <span className="text-muted-foreground font-normal">(admin-configured · {activeSem?.totalWeeks ?? 18} weeks)</span>
                </label>
                <div className="flex flex-wrap gap-1.5 mb-3">
                  {weekOptions.map((w, i) => (
                    <button
                      key={w}
                      onClick={() => set("academicWeek")(w)}
                      className={`text-xs px-2.5 py-1.5 rounded-lg border font-medium transition-all ${
                        f.academicWeek === w
                          ? "bg-primary text-white border-primary shadow-sm"
                          : "border-border text-muted-foreground hover:border-primary/40 hover:bg-primary/5"
                      }`}
                    >
                      W{i + 1}
                    </button>
                  ))}
                </div>
                <div className="grid grid-cols-2 gap-3">
                  <Field label="Date Covered" value={f.dateCovered} onChange={set("dateCovered")} placeholder="e.g. June 3–27, 2026" span={2} />
                  <Field label="Units" value={f.unit} onChange={set("unit")} placeholder="e.g. 3" />
                  <Field label="Duration" value={f.duration} onChange={set("duration")} placeholder="e.g. 3 hrs." />
                  <Field label="Date Conducted" value={f.dateConducted} onChange={set("dateConducted")} />
                  <Field label="Time Conducted" value={f.timeConducted} onChange={set("timeConducted")} placeholder="7:30 AM – 1:00 PM" />
                </div>
              </div>
              <div className="bg-card border border-card-border rounded-2xl p-5">
                <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-4">Topics Covered</p>
                <CountedTextarea
                  label="Topics covered this period"
                  value={f.topicsCovered}
                  onChange={set("topicsCovered")}
                  rows={6}
                  placeholder="List all topics discussed..."
                  max={800}
                />
              </div>
            </div>
          )}

          {/* STEP 3 — Activities & Photos */}
          {step === 3 && (
            <div className="space-y-4 animate-in fade-in slide-in-from-right-4 duration-200">
              <div className="bg-card border border-card-border rounded-2xl p-5 space-y-4">
                <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                  <BarChart3 size={11} /> Activities & Documentation
                </p>
                <CountedTextarea
                  label="Synchronous activities conducted"
                  value={f.activitiesConducted}
                  onChange={set("activitiesConducted")}
                  rows={4}
                  placeholder="PowerPoint Presentation, Q & A, group work..."
                  max={500}
                />
                <CountedTextarea
                  label="Asynchronous activities"
                  value={f.asyncActivities}
                  onChange={set("asyncActivities")}
                  rows={3}
                  placeholder="Specify activities done outside class time (module reading, online quiz, etc.)"
                  max={400}
                />
                <CountedTextarea
                  label="Laboratory activities"
                  value={f.labActivities}
                  onChange={set("labActivities")}
                  rows={3}
                  placeholder="Specify lab activities performed (coding, simulation, etc.)"
                  max={400}
                />
                <div className="grid grid-cols-2 gap-3 pt-1">
                  <Field label="No. of Attendees" value={f.noAttendees} onChange={set("noAttendees")} hint="Auto-filled from selected session" />
                  <Field label="No. of Absent" value={f.noAbsent} onChange={set("noAbsent")} hint="Auto-filled from selected session" />
                </div>
              </div>
              <div className="bg-card border border-card-border rounded-2xl p-5">
                <PhotoZone
                  photos={f.photos}
                  onAdd={(url) => set("photos")([...f.photos, url])}
                  onRemove={(idx) => set("photos")(f.photos.filter((_, i) => i !== idx))}
                />
                <p className="text-[10px] text-muted-foreground mt-2">Photos appear on Page 2 of the printed report.</p>
              </div>
            </div>
          )}

          {/* STEP 4 — Approval */}
          {step === 4 && (
            <div className="space-y-4 animate-in fade-in slide-in-from-right-4 duration-200">
              <div className="bg-card border border-card-border rounded-2xl p-5">
                <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-4 flex items-center gap-1.5">
                  <PenLine size={11} /> Approval Section
                </p>
                <div className="grid grid-cols-2 gap-3">
                  <Field label="Faculty signature (name)" value={f.instructorName} onChange={set("instructorName")} />
                  <Field label="Dean name" value={f.deanName} onChange={set("deanName")} />
                  <Field label="Date submitted" value={f.dateSubmitted} onChange={set("dateSubmitted")} type="date" />
                  <Field label="HRD received date" value={f.hrdDate} onChange={set("hrdDate")} type="date" />
                </div>
              </div>

              {/* Completion card */}
              <div className="bg-gradient-to-br from-primary/10 to-teal-50 border border-primary/20 rounded-2xl p-5 text-center">
                <div className="w-12 h-12 rounded-full bg-primary/20 flex items-center justify-center mx-auto mb-3">
                  <Sparkles size={22} className="text-primary" />
                </div>
                <p className="text-sm font-bold text-foreground mb-1">Report complete!</p>
                <p className="text-xs text-muted-foreground mb-4">
                  All 4 steps filled in. Click <strong>Preview & Print</strong> to generate the institutional form.
                </p>
                <button
                  onClick={handlePrint}
                  className="flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 active:scale-95 transition-all mx-auto shadow-lg shadow-primary/30"
                >
                  <Printer size={15} /> Preview &amp; Print Report
                </button>
              </div>
            </div>
          )}
        </div>

        {/* Bottom nav */}
        <div className="sticky bottom-0 bg-card border-t border-border px-5 py-3 flex items-center justify-between">
          <button
            disabled={step === 1}
            onClick={() => setStep((s) => Math.max(1, s - 1))}
            className="flex items-center gap-1.5 px-4 py-2 border border-border rounded-xl text-sm font-medium text-muted-foreground hover:bg-muted disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
          >
            <ChevronLeft size={14} /> Back
          </button>

          <div className="flex items-center gap-2">
            <button onClick={handleSave} className={`text-[10px] px-3 py-1.5 rounded-lg border transition-all ${saved ? "bg-green-50 border-green-300 text-green-700" : "border-border text-muted-foreground hover:bg-muted"}`}>
              {saved ? "✓ Saved" : "Save draft"}
            </button>
            {step < 4 ? (
              <button
                onClick={() => setStep((s) => Math.min(4, s + 1))}
                className="flex items-center gap-1.5 px-5 py-2 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 active:scale-95 transition-all shadow-sm shadow-primary/30"
              >
                Next <ChevronRight size={14} />
              </button>
            ) : (
              <button
                onClick={handlePrint}
                className="flex items-center gap-1.5 px-5 py-2 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 active:scale-95 transition-all shadow-sm shadow-primary/30"
              >
                <Printer size={13} /> Preview &amp; Print
              </button>
            )}
          </div>
        </div>
      </div>

      {/* ══ RIGHT: Live preview pane (desktop only) ══ */}
      {showPreviewPane && (
        <div className="hidden lg:flex flex-1 border-l border-border flex-col overflow-hidden">
          <div className="px-4 py-2.5 border-b border-border bg-muted/30 flex items-center justify-between flex-shrink-0">
            <div className="flex items-center gap-2">
              <div className="flex gap-1">
                <span className="w-2.5 h-2.5 rounded-full bg-red-400" />
                <span className="w-2.5 h-2.5 rounded-full bg-amber-400" />
                <span className="w-2.5 h-2.5 rounded-full bg-green-400" />
              </div>
              <span className="text-[10px] font-semibold text-muted-foreground">Live Document Preview</span>
            </div>
            <div className="flex items-center gap-1.5 text-[9px] text-muted-foreground">
              <span className="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse" />
              Updates as you type
            </div>
          </div>
          <div className="flex-1 overflow-hidden p-3 bg-muted/20">
            <LivePreview f={f} cls={cls} step={step} />
          </div>
        </div>
      )}
    </div>
  );
}
