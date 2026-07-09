import { computeKMeans, STUDENTS } from "@/data/mock";
import { Brain, Users, TrendingUp, AlertTriangle, CheckCircle } from "lucide-react";

const CLUSTER_CONFIG = {
  high:     { label: "High Performers",  color: "text-green-600",  bg: "bg-green-500",   light: "bg-green-50 border-green-200",  icon: CheckCircle },
  average:  { label: "Average",          color: "text-amber-600",  bg: "bg-amber-500",   light: "bg-amber-50 border-amber-200",  icon: TrendingUp },
  at_risk:  { label: "At Risk",          color: "text-red-600",    bg: "bg-red-500",      light: "bg-red-50 border-red-200",      icon: AlertTriangle },
} as const;

function MiniBar({ value, max = 100, color }: { value: number; max?: number; color: string }) {
  return (
    <div className="w-full h-1.5 bg-muted rounded-full overflow-hidden">
      <div className={`h-full rounded-full transition-all ${color}`} style={{ width: `${(value / max) * 100}%` }} />
    </div>
  );
}

function ScatterDot({ x, y, cluster, name }: { x: number; y: number; cluster: string; name: string }) {
  const cfg = CLUSTER_CONFIG[cluster as keyof typeof CLUSTER_CONFIG];
  return (
    <div
      title={`${name}: Att ${x}% | Grade ${y}%`}
      className={`absolute w-3 h-3 rounded-full cursor-pointer hover:scale-150 transition-transform ${cfg.bg} border-2 border-white shadow-sm`}
      style={{
        left: `${x}%`,
        bottom: `${y}%`,
        transform: "translate(-50%, 50%)",
      }}
    />
  );
}

export default function AnalyticsView({ classId }: { classId: number }) {
  const clusters = computeKMeans();

  const grouped = {
    high:    clusters.filter((c) => c.cluster === "high"),
    average: clusters.filter((c) => c.cluster === "average"),
    at_risk: clusters.filter((c) => c.cluster === "at_risk"),
  };

  const avgAtt   = Math.round(clusters.reduce((s, c) => s + c.attendancePct, 0) / clusters.length);
  const avgGrade = Math.round(clusters.reduce((s, c) => s + c.gradeAvg, 0) / clusters.length * 10) / 10;

  return (
    <div className="p-6 space-y-5">
      {/* Header */}
      <div className="flex items-start justify-between flex-wrap gap-3">
        <div>
          <div className="flex items-center gap-2 mb-0.5">
            <Brain size={16} className="text-primary" />
            <h2 className="text-base font-semibold text-foreground">K-Means Analytics</h2>
          </div>
          <p className="text-xs text-muted-foreground">Students auto-clustered by attendance + grade data from the Grades tab</p>
        </div>
        <div className="flex items-center gap-2 bg-primary/10 text-primary px-3 py-1.5 rounded-lg text-xs font-medium">
          <Brain size={12} /> AI-powered clustering
        </div>
      </div>

      {/* Summary cards */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
        {[
          { label: "Total students", value: STUDENTS.length, sub: "enrolled", color: "text-foreground" },
          { label: "High performers", value: grouped.high.length, sub: "students", color: "text-green-600" },
          { label: "Average", value: grouped.average.length, sub: "students", color: "text-amber-600" },
          { label: "At risk", value: grouped.at_risk.length, sub: "students", color: "text-red-600" },
        ].map((card) => (
          <div key={card.label} className="bg-card border border-card-border rounded-xl p-3">
            <p className="text-[10px] text-muted-foreground">{card.label}</p>
            <p className={`text-2xl font-bold mt-0.5 ${card.color}`}>{card.value}</p>
            <p className="text-[10px] text-muted-foreground">{card.sub}</p>
          </div>
        ))}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-5 gap-5">
        {/* Scatter plot */}
        <div className="lg:col-span-3 bg-card border border-card-border rounded-xl p-5">
          <div className="flex items-center justify-between mb-4">
            <p className="text-xs font-semibold text-foreground">Attendance vs. Grade Performance</p>
            <div className="flex items-center gap-3">
              {Object.entries(CLUSTER_CONFIG).map(([key, cfg]) => (
                <div key={key} className="flex items-center gap-1">
                  <div className={`w-2 h-2 rounded-full ${cfg.bg}`} />
                  <span className="text-[10px] text-muted-foreground">{cfg.label}</span>
                </div>
              ))}
            </div>
          </div>

          {/* Scatter area */}
          <div className="relative" style={{ height: 220 }}>
            {/* Grid lines */}
            <div className="absolute inset-0 border border-border rounded-lg overflow-hidden">
              {[25, 50, 75].map((v) => (
                <div key={v} className="absolute left-0 right-0 border-t border-dashed border-border/50"
                  style={{ bottom: `${v}%` }} />
              ))}
              {[25, 50, 75].map((v) => (
                <div key={v} className="absolute top-0 bottom-0 border-l border-dashed border-border/50"
                  style={{ left: `${v}%` }} />
              ))}
              {/* Zone shading */}
              <div className="absolute right-0 top-0 w-1/4 h-1/4 bg-green-50/60 rounded-tr-lg" />
              <div className="absolute left-0 bottom-0 w-1/4 h-1/4 bg-red-50/60 rounded-bl-lg" />
              {/* Dots */}
              {clusters.map((c) => (
                <ScatterDot
                  key={c.student.id}
                  x={c.attendancePct}
                  y={c.gradeAvg}
                  cluster={c.cluster}
                  name={c.student.name}
                />
              ))}
            </div>
            {/* Axis labels */}
            <div className="absolute -bottom-5 left-0 right-0 flex justify-between text-[9px] text-muted-foreground px-1">
              <span>0%</span><span>Attendance Rate</span><span>100%</span>
            </div>
            <div className="absolute -left-6 top-0 bottom-0 flex flex-col justify-between text-[9px] text-muted-foreground">
              <span>100%</span><span className="-rotate-90 translate-y-2">Grade</span><span>0%</span>
            </div>
          </div>

          <div className="flex items-center justify-between mt-6 pt-3 border-t border-border text-xs text-muted-foreground">
            <span>Class avg attendance: <strong className="text-foreground">{avgAtt}%</strong></span>
            <span>Class avg grade: <strong className="text-foreground">{avgGrade}%</strong></span>
          </div>
        </div>

        {/* Cluster breakdown */}
        <div className="lg:col-span-2 space-y-3">
          {(["high", "average", "at_risk"] as const).map((key) => {
            const cfg = CLUSTER_CONFIG[key];
            const Icon = cfg.icon;
            const group = grouped[key];
            return (
              <div key={key} className={`border rounded-xl p-4 ${cfg.light}`}>
                <div className="flex items-center gap-2 mb-3">
                  <Icon size={14} className={cfg.color} />
                  <span className={`text-xs font-semibold ${cfg.color}`}>{cfg.label}</span>
                  <span className={`ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded-full ${cfg.bg} text-white`}>{group.length}</span>
                </div>
                <div className="space-y-2">
                  {group.map((c) => (
                    <div key={c.student.id} className="bg-white/60 rounded-lg px-3 py-2">
                      <div className="flex items-center justify-between mb-1">
                        <span className="text-xs font-medium text-foreground truncate">{c.student.name.split(",")[0]}</span>
                        <div className="flex items-center gap-2 text-[10px] text-muted-foreground flex-shrink-0 ml-2">
                          <span className={cfg.color}>{c.attendancePct}% att</span>
                          <span>·</span>
                          <span>{c.gradeAvg}% grade</span>
                        </div>
                      </div>
                      <div className="flex gap-1">
                        <div className="flex-1">
                          <MiniBar value={c.attendancePct} color={cfg.bg} />
                        </div>
                        <div className="flex-1">
                          <MiniBar value={c.gradeAvg} color={cfg.bg} />
                        </div>
                      </div>
                    </div>
                  ))}
                  {group.length === 0 && (
                    <p className="text-xs text-muted-foreground text-center py-1">No students in this cluster</p>
                  )}
                </div>
              </div>
            );
          })}
        </div>
      </div>

      {/* Cluster stats table */}
      <div className="bg-card border border-card-border rounded-xl overflow-hidden">
        <div className="px-4 py-3 border-b border-border">
          <p className="text-xs font-semibold text-foreground">Cluster statistics</p>
        </div>
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b border-border bg-muted/30">
              {["Cluster", "Students", "Avg attendance", "Avg grade", "Recommendation"].map((h) => (
                <th key={h} className="text-left text-[10px] font-medium text-muted-foreground px-4 py-2">{h}</th>
              ))}
            </tr>
          </thead>
          <tbody>
            {(["high", "average", "at_risk"] as const).map((key) => {
              const cfg = CLUSTER_CONFIG[key];
              const group = grouped[key];
              const avgA = group.length ? Math.round(group.reduce((s, c) => s + c.attendancePct, 0) / group.length) : 0;
              const avgG = group.length ? Math.round(group.reduce((s, c) => s + c.gradeAvg, 0) / group.length * 10) / 10 : 0;
              const recs: Record<string, string> = {
                high:    "Continue enrichment activities",
                average: "Provide additional practice exercises",
                at_risk: "Schedule individual consultation",
              };
              return (
                <tr key={key} className="border-b border-border last:border-0">
                  <td className="px-4 py-2.5">
                    <div className="flex items-center gap-1.5">
                      <div className={`w-2 h-2 rounded-full ${cfg.bg}`} />
                      <span className={`text-xs font-medium ${cfg.color}`}>{cfg.label}</span>
                    </div>
                  </td>
                  <td className="px-4 py-2.5 text-xs text-foreground font-semibold">{group.length}</td>
                  <td className="px-4 py-2.5 text-xs text-foreground">{avgA}%</td>
                  <td className="px-4 py-2.5 text-xs text-foreground">{avgG}%</td>
                  <td className="px-4 py-2.5 text-xs text-muted-foreground italic">{recs[key]}</td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
    </div>
  );
}
