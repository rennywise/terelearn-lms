import { useState } from "react";
import { UserPlus, Search, Mail, Hash, Copy, Link, Users } from "lucide-react";
import { STUDENTS, getStudentAttendancePct, getStudentGradeAverage } from "@/data/mock";

const CLASS_CODE = "3309BE4";

const AVATAR_COLORS = [
  "bg-teal-600", "bg-blue-600", "bg-violet-600",
  "bg-rose-600", "bg-amber-600", "bg-cyan-600", "bg-green-700", "bg-pink-600",
];

export default function PeopleTab({ classId }: { classId: number }) {
  const [search, setSearch] = useState("");
  const [codeTab, setCodeTab] = useState<"code" | "link">("code");

  const students = STUDENTS.filter((s) => s.classId === classId);
  const filtered = students.filter((s) =>
    s.name.toLowerCase().includes(search.toLowerCase()) ||
    s.studentNumber.includes(search)
  );

  return (
    <div className="flex gap-0 min-h-[calc(100vh-160px)]">
      {/* ── Main student list ── */}
      <div className="flex-1 min-w-0 px-5 py-5">
        {/* Search bar */}
        <div className="flex items-center gap-3 mb-4">
          <div className="flex items-center gap-2 bg-card border border-border rounded-lg px-3 py-2 flex-1 max-w-sm">
            <Search size={13} className="text-muted-foreground flex-shrink-0" />
            <input
              data-testid="input-people-search"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search by name or student #..."
              className="text-sm bg-transparent outline-none w-full text-foreground placeholder:text-muted-foreground"
            />
          </div>
        </div>

        {/* STUDENTS header */}
        <div className="flex items-center justify-between mb-2 px-1">
          <span className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
            <Users size={11} /> Students
          </span>
          <span className="text-xs font-bold text-muted-foreground">{filtered.length}</span>
        </div>

        {/* Student rows */}
        <div className="space-y-2">
          {filtered.map((s, idx) => {
            const att  = getStudentAttendancePct(s.id);
            const grade = getStudentGradeAverage(s.id);
            const avatarBg = AVATAR_COLORS[idx % AVATAR_COLORS.length];
            const initials = s.name.split(",")[0]?.trim().charAt(0).toUpperCase() +
              (s.name.split(",")[1]?.trim().charAt(0).toUpperCase() ?? "");

            return (
              <div
                key={s.id}
                data-testid={`row-student-${s.id}`}
                className="bg-card border border-card-border rounded-xl px-4 py-3 flex items-center gap-3 hover:border-primary/30 transition-colors"
              >
                {/* Avatar */}
                <div className={`w-10 h-10 rounded-full ${avatarBg} text-white flex items-center justify-center text-sm font-bold flex-shrink-0 relative`}>
                  {initials}
                  {/* Online dot */}
                  <span className="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-400 border-2 border-card rounded-full" />
                </div>

                {/* Info */}
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-semibold text-foreground truncate">{s.name.split(",").reverse().map(n => n.trim()).join(" ")}</p>
                  <p className="text-xs text-muted-foreground truncate flex items-center gap-1">
                    <Mail size={10} /> {s.email}
                  </p>
                  <div className="flex items-center gap-2 mt-1">
                    <span className="text-[10px] px-1.5 py-0.5 bg-primary/10 text-primary rounded font-medium">
                      {s.studentNumber.split("-")[0] === "2024" ? "BSIT" : "BSIT"}
                    </span>
                    <span className="flex items-center gap-0.5 text-[10px] text-muted-foreground">
                      <Hash size={9} />{s.studentNumber}
                    </span>
                  </div>
                </div>

                {/* Attendance & grade */}
                <div className="hidden md:flex flex-col items-end gap-1 mr-3">
                  <span className={`text-[10px] font-semibold ${att >= 80 ? "text-green-600" : att >= 50 ? "text-amber-600" : "text-red-600"}`}>
                    {att}% att.
                  </span>
                  <span className={`text-[10px] font-semibold ${grade >= 85 ? "text-green-600" : grade >= 75 ? "text-amber-600" : "text-red-600"}`}>
                    {grade}% avg
                  </span>
                </div>

                {/* Status + actions */}
                <div className="flex flex-col items-end gap-1.5">
                  <span className="text-[10px] font-semibold px-2 py-0.5 bg-green-100 text-green-700 rounded-full border border-green-200">
                    Enrolled
                  </span>
                  <button className="flex items-center gap-1 text-[10px] text-red-500 hover:text-red-600 border border-red-200 hover:bg-red-50 px-2 py-0.5 rounded-lg transition-colors">
                    🗑 Remove
                  </button>
                </div>
              </div>
            );
          })}

          {filtered.length === 0 && (
            <div className="text-center py-12 text-muted-foreground text-sm">No students found.</div>
          )}
        </div>

        <p className="text-xs text-muted-foreground mt-4">{filtered.length} student{filtered.length !== 1 ? "s" : ""} enrolled</p>
      </div>

      {/* ── Right sidebar ── */}
      <aside className="w-64 flex-shrink-0 border-l border-border py-5 px-4 hidden lg:block">
        <div className="bg-card border border-card-border rounded-xl p-4">
          <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">Add Students</p>
          <p className="text-xs text-muted-foreground mb-3">Share the class code or invite link so students can join.</p>

          <button
            data-testid="btn-add-student"
            className="w-full flex items-center justify-center gap-1.5 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors mb-3"
          >
            <UserPlus size={13} /> Invite Students
          </button>

          <p className="text-2xl font-bold text-primary tracking-widest text-center py-2 font-mono">
            {CLASS_CODE}
          </p>

          <div className="flex gap-1.5 mt-2">
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
            <div className="mt-2 bg-muted rounded-lg px-2 py-1.5 flex items-center gap-1">
              <span className="text-[10px] text-muted-foreground truncate flex-1">
                http://localhost/terelearn/join/{CLASS_CODE}
              </span>
              <Copy size={9} className="text-muted-foreground flex-shrink-0" />
            </div>
          )}
        </div>
      </aside>
    </div>
  );
}
