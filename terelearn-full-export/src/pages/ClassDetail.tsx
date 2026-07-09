import { useState } from "react";
import { useLocation, useParams } from "wouter";
import { ArrowLeft, Settings, Sun, Moon } from "lucide-react";
import { CLASSES } from "@/data/mock";
import StreamTab from "@/components/tabs/StreamTab";
import PeopleTab from "@/components/tabs/PeopleTab";
import GradesTab from "@/components/tabs/GradesTab";
import ReportTab from "@/components/tabs/ReportTab";

type Tab = "stream" | "people" | "grades" | "report";

export default function ClassDetail() {
  const params = useParams<{ id: string }>();
  const [, setLocation] = useLocation();
  const [activeTab, setActiveTab] = useState<Tab>("stream");
  const [dark, setDark] = useState(false);

  const cls = CLASSES.find((c) => c.id === Number(params.id));
  if (!cls) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <p className="text-muted-foreground">Class not found.</p>
      </div>
    );
  }

  const handleDark = () => {
    setDark((d) => !d);
    document.documentElement.classList.toggle("dark");
  };

  const tabs: { id: Tab; label: string; badge?: number }[] = [
    { id: "stream", label: "Stream" },
    { id: "people", label: "People", badge: 8 },
    { id: "grades", label: "Grades" },
    { id: "report", label: "Report" },
  ];

  return (
    <div className="min-h-screen bg-background">
      {/* Global nav */}
      <header className="bg-primary text-primary-foreground sticky top-0 z-50 shadow-sm">
        <div className="px-4 py-2.5 flex items-center gap-3">
          <button
            data-testid="btn-back"
            onClick={() => setLocation("/")}
            className="p-1.5 rounded hover:bg-white/15 transition-colors"
          >
            <ArrowLeft size={16} />
          </button>
          <span className="text-sm font-medium truncate flex-1">
            {cls.section} {cls.name}
          </span>
          <button
            onClick={handleDark}
            className="p-1.5 rounded hover:bg-white/15 transition-colors"
          >
            {dark ? <Sun size={15} /> : <Moon size={15} />}
          </button>
          <button className="p-1.5 rounded hover:bg-white/15 transition-colors">
            <Settings size={15} />
          </button>
        </div>
      </header>

      {/* Class hero */}
      <div
        className="relative px-6 pt-7 pb-5"
        style={{
          background:
            "linear-gradient(135deg, hsl(145 61% 16%) 0%, hsl(145 55% 26%) 60%, hsl(160 50% 32%) 100%)",
        }}
      >
        <div className="max-w-5xl mx-auto">
          <h1 className="text-xl font-bold text-white leading-tight">
            {cls.section} {cls.name}
          </h1>
          <p className="text-white/70 text-xs mt-0.5">{cls.program}</p>
          <div className="flex flex-wrap items-center gap-3 mt-3 text-white/80 text-xs">
            <span className="flex items-center gap-1.5 bg-white/10 px-2.5 py-1 rounded-full">
              <span>📅</span> {cls.semester} {cls.schoolYear}
            </span>
            <span className="flex items-center gap-1.5 bg-white/10 px-2.5 py-1 rounded-full">
              <span>📆</span> {cls.schedule}
            </span>
            <span className="flex items-center gap-1.5 bg-white/10 px-2.5 py-1 rounded-full">
              <span>🕐</span> {cls.timeStart} – {cls.timeEnd}
            </span>
          </div>
        </div>
      </div>

      {/* Tab bar */}
      <div className="bg-card border-b border-border sticky top-[41px] z-40">
        <div className="max-w-5xl mx-auto px-6 flex items-center gap-0">
          {tabs.map((t) => (
            <button
              key={t.id}
              data-testid={`tab-${t.id}`}
              onClick={() => setActiveTab(t.id)}
              className={`flex items-center gap-1.5 px-4 py-3 text-sm border-b-2 transition-all whitespace-nowrap ${
                activeTab === t.id
                  ? "border-primary text-primary font-medium"
                  : "border-transparent text-muted-foreground hover:text-foreground"
              }`}
            >
              {t.label}
              {t.badge !== undefined && (
                <span
                  className={`text-[10px] px-1.5 py-0.5 rounded-full font-medium ${
                    activeTab === t.id
                      ? "bg-primary text-white"
                      : "bg-muted text-muted-foreground"
                  }`}
                >
                  {t.badge}
                </span>
              )}
            </button>
          ))}
        </div>
      </div>

      {/* Tab content */}
      <div className="max-w-5xl mx-auto">
        {activeTab === "stream" && <StreamTab classId={cls.id} cls={cls} />}
        {activeTab === "people" && <PeopleTab classId={cls.id} />}
        {activeTab === "grades" && <GradesTab classId={cls.id} />}
        {activeTab === "report" && <ReportTab classId={cls.id} cls={cls} />}
      </div>
    </div>
  );
}
