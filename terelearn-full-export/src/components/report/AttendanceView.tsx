import { useState } from "react";
import {
  ChevronLeft, ChevronRight, CalendarPlus, FileDown, Sheet,
  AlertTriangle, TrendingUp, TrendingDown, Calendar
} from "lucide-react";
import {
  SESSIONS, STUDENTS, ATTENDANCE,
  getSessionAttendancePct, getAttendanceFlags,
  ClassData, AttendanceStatus
} from "@/data/mock";
import { format, startOfMonth, endOfMonth, eachDayOfInterval, getDay, isSameDay, parseISO } from "date-fns";

const STATUS_COLOR: Record<AttendanceStatus, string> = {
  present: "bg-green-500",
  late:    "bg-amber-500",
  absent:  "bg-red-500",
  excused: "bg-blue-400",
};

function pctColor(pct: number) {
  if (pct >= 80) return { bg: "bg-green-500", text: "text-white", light: "bg-green-100 text-green-800" };
  if (pct >= 50) return { bg: "bg-amber-500", text: "text-white", light: "bg-amber-100 text-amber-800" };
  return { bg: "bg-red-500", text: "text-white", light: "bg-red-100 text-red-800" };
}

// ── Log Session Modal (inline) ─────────────────────────────────────────────────
function LogSessionModal({ onClose }: { onClose: () => void }) {
  const [date, setDate] = useState(format(new Date(), "yyyy-MM-dd"));
  const [topics, setTopics] = useState("");
  const [statuses, setStatuses] = useState<Record<number, AttendanceStatus>>(
    Object.fromEntries(STUDENTS.map((s) => [s.id, "present" as AttendanceStatus]))
  );

  const statusCycle: AttendanceStatus[] = ["present", "late", "absent", "excused"];

  return (
    <div className="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40 backdrop-blur-sm p-4">
      <div className="bg-card border border-card-border rounded-2xl shadow-lg w-full max-w-md max-h-[90vh] flex flex-col">
        {/* Header */}
        <div className="flex items-center justify-between px-5 py-4 border-b border-border">
          <div>
            <h3 className="font-semibold text-foreground text-sm">Log Session</h3>
            <p className="text-xs text-muted-foreground">Record today's attendance</p>
          </div>
          <button onClick={onClose} className="text-muted-foreground hover:text-foreground text-lg leading-none">&times;</button>
        </div>

        <div className="flex-1 overflow-y-auto px-5 py-4 space-y-4">
          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="text-xs font-medium text-foreground mb-1 block">Date</label>
              <input
                type="date"
                value={date}
                onChange={(e) => setDate(e.target.value)}
                className="w-full text-sm border border-input rounded-lg px-3 py-2 bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-ring"
              />
            </div>
            <div>
              <label className="text-xs font-medium text-foreground mb-1 block">Topics covered</label>
              <input
                value={topics}
                onChange={(e) => setTopics(e.target.value)}
                placeholder="e.g. React Hooks"
                className="w-full text-sm border border-input rounded-lg px-3 py-2 bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-ring placeholder:text-muted-foreground"
              />
            </div>
          </div>

          <div>
            <div className="flex items-center justify-between mb-2">
              <span className="text-xs font-medium text-foreground">Student attendance</span>
              <span className="text-[10px] text-muted-foreground">Tap to cycle: Present → Late → Absent → Excused</span>
            </div>
            <div className="space-y-1.5">
              {STUDENTS.map((s, i) => {
                const st = statuses[s.id];
                const colors = STATUS_COLOR[st];
                return (
                  <div key={s.id} className="flex items-center justify-between py-1.5 px-2 rounded-lg hover:bg-muted/40">
                    <div className="flex items-center gap-2">
                      <span className="text-xs text-muted-foreground w-4">{i + 1}</span>
                      <span className="text-sm text-foreground">{s.name}</span>
                    </div>
                    <button
                      onClick={() => {
                        const idx = statusCycle.indexOf(st);
                        setStatuses({ ...statuses, [s.id]: statusCycle[(idx + 1) % 4] });
                      }}
                      className={`text-[10px] font-semibold px-2.5 py-1 rounded-full text-white capitalize transition-all ${colors}`}
                    >
                      {st}
                    </button>
                  </div>
                );
              })}
            </div>
          </div>
        </div>

        <div className="px-5 py-4 border-t border-border flex gap-2">
          <button onClick={onClose} className="flex-1 px-4 py-2 border border-border rounded-lg text-sm text-muted-foreground hover:bg-muted transition-colors">
            Cancel
          </button>
          <button onClick={onClose} className="flex-1 px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors">
            Save session
          </button>
        </div>
      </div>
    </div>
  );
}

// ── Attendance detail modal ────────────────────────────────────────────────────
function SessionDetailModal({ sessionId, onClose }: { sessionId: number; onClose: () => void }) {
  const session = SESSIONS.find((s) => s.id === sessionId);
  if (!session) return null;
  const records = ATTENDANCE.filter((a) => a.sessionId === sessionId);

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
      <div className="bg-card border border-card-border rounded-2xl shadow-lg w-full max-w-sm">
        <div className="flex items-center justify-between px-5 py-4 border-b border-border">
          <div>
            <h3 className="font-semibold text-foreground text-sm">{format(parseISO(session.date), "MMMM d, yyyy")}</h3>
            <p className="text-xs text-muted-foreground mt-0.5">{session.topics}</p>
          </div>
          <button onClick={onClose} className="text-muted-foreground hover:text-foreground text-lg leading-none">&times;</button>
        </div>
        <div className="px-5 py-4 space-y-1.5 max-h-72 overflow-y-auto">
          {records.map((r) => {
            const student = STUDENTS.find((s) => s.id === r.studentId);
            return (
              <div key={r.id} className="flex items-center justify-between py-1">
                <span className="text-sm text-foreground">{student?.name}</span>
                <span className={`text-[10px] font-semibold px-2 py-0.5 rounded-full text-white capitalize ${STATUS_COLOR[r.status]}`}>
                  {r.status}
                </span>
              </div>
            );
          })}
        </div>
      </div>
    </div>
  );
}

// ── Main component ─────────────────────────────────────────────────────────────
export default function AttendanceView({ classId, cls }: { classId: number; cls: ClassData }) {
  const [viewDate, setViewDate] = useState(new Date(2026, 5, 1)); // June 2026
  const [showLog, setShowLog] = useState(false);
  const [detailSession, setDetailSession] = useState<number | null>(null);

  const monthStart = startOfMonth(viewDate);
  const monthEnd   = endOfMonth(viewDate);
  const days       = eachDayOfInterval({ start: monthStart, end: monthEnd });
  const startPad   = getDay(monthStart);

  const sessions  = SESSIONS.filter((s) => s.classId === classId);
  const flags     = getAttendanceFlags();

  const allPcts   = sessions.map((s) => getSessionAttendancePct(s.id));
  const avgAtt    = allPcts.length ? Math.round(allPcts.reduce((a, b) => a + b, 0) / allPcts.length) : 0;
  const bestSession  = sessions.reduce((a, b) => getSessionAttendancePct(a.id) >= getSessionAttendancePct(b.id) ? a : b, sessions[0]);
  const worstSession = sessions.reduce((a, b) => getSessionAttendancePct(a.id) <= getSessionAttendancePct(b.id) ? a : b, sessions[0]);
  const totalAbsences = ATTENDANCE.filter((a) => a.status === "absent").length;

  return (
    <div className="p-6 space-y-5">
      {showLog && <LogSessionModal onClose={() => setShowLog(false)} />}
      {detailSession !== null && <SessionDetailModal sessionId={detailSession} onClose={() => setDetailSession(null)} />}

      {/* Action bar */}
      <div className="flex items-center justify-between flex-wrap gap-3">
        <div>
          <h2 className="text-base font-semibold text-foreground">Attendance Calendar</h2>
          <p className="text-xs text-muted-foreground">{cls.semester} {cls.schoolYear} &middot; {format(monthStart, "MMMM yyyy")}</p>
        </div>
        <div className="flex items-center gap-2">
          <button
            data-testid="btn-log-session"
            onClick={() => setShowLog(true)}
            className="flex items-center gap-1.5 px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-medium hover:bg-primary/90 transition-colors"
          >
            <CalendarPlus size={12} /> Log session
          </button>
          <button className="flex items-center gap-1.5 px-3 py-1.5 border border-border rounded-lg text-xs text-muted-foreground hover:bg-muted transition-colors">
            <FileDown size={12} /> PDF
          </button>
          <button className="flex items-center gap-1.5 px-3 py-1.5 border border-border rounded-lg text-xs text-muted-foreground hover:bg-muted transition-colors">
            <Sheet size={12} /> Excel
          </button>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {/* Calendar */}
        <div className="lg:col-span-2 bg-card border border-card-border rounded-xl p-4">
          {/* Month nav */}
          <div className="flex items-center justify-between mb-4">
            <button onClick={() => setViewDate(new Date(viewDate.getFullYear(), viewDate.getMonth() - 1))}
              className="p-1.5 rounded hover:bg-muted transition-colors">
              <ChevronLeft size={15} className="text-muted-foreground" />
            </button>
            <div className="flex items-center gap-3">
              <span className="text-sm font-semibold text-foreground">{format(viewDate, "MMMM yyyy")}</span>
              <button
                onClick={() => setViewDate(new Date(2026, 5, 1))}
                className="text-[10px] px-2 py-0.5 rounded bg-primary/10 text-primary font-medium hover:bg-primary/20 transition-colors"
              >
                Today
              </button>
            </div>
            <button onClick={() => setViewDate(new Date(viewDate.getFullYear(), viewDate.getMonth() + 1))}
              className="p-1.5 rounded hover:bg-muted transition-colors">
              <ChevronRight size={15} className="text-muted-foreground" />
            </button>
          </div>

          {/* Weekday headers */}
          <div className="grid grid-cols-7 mb-1">
            {["Sun","Mon","Tue","Wed","Thu","Fri","Sat"].map((d) => (
              <div key={d} className="text-center text-[10px] font-medium text-muted-foreground py-1">{d}</div>
            ))}
          </div>

          {/* Days grid */}
          <div className="grid grid-cols-7 gap-1">
            {Array.from({ length: startPad }).map((_, i) => <div key={`pad-${i}`} />)}
            {days.map((day) => {
              const session = sessions.find((s) => isSameDay(parseISO(s.date), day));
              const pct     = session ? getSessionAttendancePct(session.id) : null;
              const colors  = pct !== null ? pctColor(pct) : null;
              const isToday = isSameDay(day, new Date(2026, 5, 27));

              return (
                <div
                  key={day.toISOString()}
                  data-testid={`cal-day-${format(day, "yyyy-MM-dd")}`}
                  onClick={() => session && setDetailSession(session.id)}
                  className={`relative aspect-square rounded-lg flex flex-col items-center justify-center transition-all text-center
                    ${session ? "cursor-pointer hover:opacity-90" : ""}
                    ${colors ? `${colors.bg}` : isToday ? "ring-2 ring-primary/50 bg-primary/5" : "bg-muted/30"}
                  `}
                >
                  <span className={`text-xs font-semibold ${colors ? colors.text : isToday ? "text-primary" : "text-foreground"}`}>
                    {format(day, "d")}
                  </span>
                  {pct !== null && (
                    <span className={`text-[8px] font-medium ${colors?.text}`}>{pct}%</span>
                  )}
                </div>
              );
            })}
          </div>

          {/* Legend */}
          <div className="flex items-center gap-4 mt-4 pt-3 border-t border-border">
            {[
              { label: "≥ 80%", cls: "bg-green-500" },
              { label: "50–79%", cls: "bg-amber-500" },
              { label: "< 50%", cls: "bg-red-500" },
              { label: "No session", cls: "bg-muted/60 border border-border" },
            ].map((l) => (
              <div key={l.label} className="flex items-center gap-1">
                <div className={`w-2.5 h-2.5 rounded-sm ${l.cls}`} />
                <span className="text-[10px] text-muted-foreground">{l.label}</span>
              </div>
            ))}
          </div>
        </div>

        {/* Stats sidebar */}
        <div className="space-y-3">
          {/* Month snapshot */}
          <div className="bg-card border border-card-border rounded-xl p-4">
            <p className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wider mb-3">Month snapshot</p>
            <div className="grid grid-cols-2 gap-3">
              <div className="bg-muted/40 rounded-lg p-3">
                <p className="text-[10px] text-muted-foreground">Sessions recorded</p>
                <p className="text-xl font-bold text-foreground mt-0.5">{sessions.length}</p>
              </div>
              <div className="bg-muted/40 rounded-lg p-3">
                <p className="text-[10px] text-muted-foreground">Avg. attendance</p>
                <p className={`text-xl font-bold mt-0.5 ${avgAtt >= 80 ? "text-green-600" : avgAtt >= 50 ? "text-amber-600" : "text-red-600"}`}>
                  {avgAtt}%
                </p>
              </div>
            </div>
          </div>

          {/* Best / Worst */}
          {bestSession && (
            <div className="bg-card border border-card-border rounded-xl p-4 space-y-2.5">
              <p className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wider">Session highlights</p>
              <div className="flex items-start gap-2">
                <TrendingUp size={13} className="text-green-600 mt-0.5 flex-shrink-0" />
                <div>
                  <p className="text-[10px] text-muted-foreground">Best day</p>
                  <p className="text-xs font-semibold text-green-600">
                    {format(parseISO(bestSession.date), "MMM d")} &middot; {getSessionAttendancePct(bestSession.id)}%
                  </p>
                </div>
              </div>
              {worstSession && (
                <div className="flex items-start gap-2">
                  <TrendingDown size={13} className="text-red-500 mt-0.5 flex-shrink-0" />
                  <div>
                    <p className="text-[10px] text-muted-foreground">Lowest day</p>
                    <p className="text-xs font-semibold text-red-500">
                      {format(parseISO(worstSession.date), "MMM d")} &middot; {getSessionAttendancePct(worstSession.id)}%
                    </p>
                  </div>
                </div>
              )}
            </div>
          )}

          {/* Attendance flags */}
          <div className="bg-card border border-card-border rounded-xl p-4">
            <div className="flex items-center justify-between mb-3">
              <p className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wider">Attendance flags</p>
              {flags.length > 0 && (
                <span className="text-[9px] bg-red-100 text-red-700 px-1.5 py-0.5 rounded-full font-medium">{flags.length} at risk</span>
              )}
            </div>

            <div className="grid grid-cols-2 gap-2 mb-3">
              <div className="bg-muted/40 rounded-lg p-2.5">
                <p className="text-[9px] text-muted-foreground">Absence marks</p>
                <p className="text-base font-bold text-foreground">{totalAbsences}</p>
              </div>
              <div className="bg-muted/40 rounded-lg p-2.5">
                <p className="text-[9px] text-muted-foreground">Students at risk</p>
                <p className="text-base font-bold text-red-500">{flags.length}</p>
              </div>
            </div>

            {flags.length > 0 ? (
              <div className="space-y-1.5">
                {flags.map((f) => (
                  <div key={f.student.id} className="flex items-center justify-between py-1 border-t border-border first:border-0">
                    <div className="flex items-center gap-1.5">
                      <AlertTriangle size={10} className="text-amber-500 flex-shrink-0" />
                      <span className="text-xs text-foreground truncate max-w-[100px]">{f.student.name.split(",")[0]}</span>
                    </div>
                    <span className="text-[10px] font-medium text-red-500">{f.pct}%</span>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-xs text-muted-foreground text-center py-2">All students above 80%</p>
            )}
          </div>

          {/* Session list */}
          <div className="bg-card border border-card-border rounded-xl p-4">
            <p className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wider mb-3">Sessions</p>
            <div className="space-y-1.5">
              {sessions.slice().reverse().map((s) => {
                const pct = getSessionAttendancePct(s.id);
                const c = pctColor(pct);
                return (
                  <div
                    key={s.id}
                    onClick={() => setDetailSession(s.id)}
                    className="flex items-center justify-between cursor-pointer hover:bg-muted/30 rounded-lg px-2 py-1.5 transition-colors"
                  >
                    <div className="flex items-center gap-2">
                      <Calendar size={11} className="text-muted-foreground flex-shrink-0" />
                      <span className="text-xs text-foreground">{format(parseISO(s.date), "MMM d")}</span>
                    </div>
                    <span className={`text-[10px] font-semibold px-1.5 py-0.5 rounded ${c.light}`}>{pct}%</span>
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
