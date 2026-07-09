import { useState } from "react";
import { CalendarDays, BarChart3, FileText, ChevronRight } from "lucide-react";
import { ClassData } from "@/data/mock";
import AttendanceView from "@/components/report/AttendanceView";
import AnalyticsView from "@/components/report/AnalyticsView";
import AccomplishmentView from "@/components/report/AccomplishmentView";

type ReportSection = "attendance" | "analytics" | "accomplishment";

const SECTIONS: { id: ReportSection; label: string; sub: string; icon: typeof CalendarDays }[] = [
  { id: "attendance",    label: "Attendance",           sub: "Log sessions & calendar",    icon: CalendarDays },
  { id: "analytics",    label: "Analytics",             sub: "K-Means clustering",         icon: BarChart3 },
  { id: "accomplishment", label: "Accomplishment Report", sub: "Auto-filled from data",    icon: FileText },
];

export default function ReportTab({ classId, cls }: { classId: number; cls: ClassData }) {
  const [section, setSection] = useState<ReportSection>("accomplishment");

  return (
    <div className="flex min-h-[calc(100vh-148px)]">
      {/* Left sidebar */}
      <aside className="w-52 flex-shrink-0 border-r border-border py-4 px-2 hidden sm:block">
        <p className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wider px-2 mb-2">Report sections</p>
        {SECTIONS.map((s, i) => {
          const Icon = s.icon;
          const active = section === s.id;
          return (
            <button
              key={s.id}
              data-testid={`nav-report-${s.id}`}
              onClick={() => setSection(s.id)}
              className={`w-full flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-left transition-all mb-0.5 group ${
                active ? "bg-primary/10 text-primary" : "text-muted-foreground hover:bg-muted hover:text-foreground"
              }`}
            >
              <div className={`w-6 h-6 rounded flex items-center justify-center flex-shrink-0 ${
                active ? "bg-primary text-white" : "bg-muted text-muted-foreground group-hover:bg-muted/70"
              }`}>
                <Icon size={13} />
              </div>
              <div className="flex-1 min-w-0">
                <div className={`text-xs font-medium leading-tight ${active ? "text-primary" : ""}`}>{s.label}</div>
              </div>
              {active && <ChevronRight size={11} className="text-primary flex-shrink-0" />}
              {/* Step number */}
              <span className={`text-[9px] px-1 rounded font-bold ${active ? "bg-primary/20 text-primary" : "bg-muted text-muted-foreground"}`}>
                {i + 1}
              </span>
            </button>
          );
        })}

        <div className="mt-4 pt-3 border-t border-border px-2">
          <p className="text-[9px] text-muted-foreground leading-relaxed">
            Grades from the Grades tab auto-populate analytics and the report.
          </p>
        </div>
      </aside>

      {/* Mobile section switcher */}
      <div className="sm:hidden border-b border-border bg-card w-full flex">
        {SECTIONS.map((s) => {
          const Icon = s.icon;
          const active = section === s.id;
          return (
            <button
              key={s.id}
              onClick={() => setSection(s.id)}
              className={`flex-1 flex flex-col items-center gap-1 py-2.5 text-xs transition-colors border-b-2 ${
                active ? "border-primary text-primary font-medium" : "border-transparent text-muted-foreground"
              }`}
            >
              <Icon size={14} />
              <span className="text-[10px]">{s.label}</span>
            </button>
          );
        })}
      </div>

      {/* Main content */}
      <main className="flex-1 min-w-0">
        {section === "attendance"     && <AttendanceView classId={classId} cls={cls} />}
        {section === "analytics"      && <AnalyticsView classId={classId} />}
        {section === "accomplishment" && <AccomplishmentView classId={classId} cls={cls} />}
      </main>
    </div>
  );
}
