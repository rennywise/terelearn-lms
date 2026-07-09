export interface Semester {
  id: number;
  label: string;       // "1st Semester" | "2nd Semester"
  schoolYear: string;  // "2027-2028"
  startDate: string;
  endDate: string;
  totalWeeks: number;  // admin-configurable
  isActive: boolean;
}

export interface Program {
  id: number;
  code: string;
  name: string;
  color: string;
  sectionsByYear: Record<number, number>; // { 1: 5, 2: 3, 3: 4, 4: 2 }
}

export interface Department {
  id: number;
  code: string;
  name: string;
  programIds: number[];
}

// ── Semesters (admin-managed) ────────────────────────────────────────────────
export const SEMESTERS: Semester[] = [
  {
    id: 1,
    label: "1st Semester",
    schoolYear: "2027-2028",
    startDate: "2026-01-01",
    endDate: "2026-08-01",
    totalWeeks: 18,
    isActive: true,
  },
  {
    id: 2,
    label: "2nd Semester",
    schoolYear: "2027-2028",
    startDate: "2027-10-12",
    endDate: "2028-05-01",
    totalWeeks: 18,
    isActive: false,
  },
  {
    id: 3,
    label: "2nd Semester",
    schoolYear: "2026-2027",
    startDate: "2026-08-12",
    endDate: "2027-05-20",
    totalWeeks: 16,
    isActive: false,
  },
];

// ── Programs ─────────────────────────────────────────────────────────────────
export const PROGRAMS: Program[] = [
  {
    id: 1,
    code: "BSIT",
    name: "Bachelor of Science in Information Technology",
    color: "bg-teal-500",
    sectionsByYear: { 1: 5, 2: 3, 3: 4, 4: 2 },
  },
  {
    id: 2,
    code: "BSTM",
    name: "Bachelor of Science in Tourism Management",
    color: "bg-violet-500",
    sectionsByYear: { 1: 4 },
  },
  {
    id: 3,
    code: "BSBA",
    name: "Bachelor of Science in Business Administration",
    color: "bg-amber-500",
    sectionsByYear: { 1: 3, 2: 3, 3: 2 },
  },
];

// ── Departments ──────────────────────────────────────────────────────────────
export const DEPARTMENTS: Department[] = [
  { id: 1, code: "CITC", name: "College of Information Technology and Computing", programIds: [1] },
  { id: 2, code: "COT",  name: "College of Tourism",   programIds: [2] },
  { id: 3, code: "COBA", name: "College of Business Administration", programIds: [3] },
];

export function getActiveSemester(): Semester | undefined {
  return SEMESTERS.find((s) => s.isActive);
}

export function getWeekOptions(semesterId?: number): string[] {
  const sem = semesterId
    ? SEMESTERS.find((s) => s.id === semesterId)
    : getActiveSemester();
  const total = sem?.totalWeeks ?? 18;
  return Array.from({ length: total }, (_, i) => {
    const n = i + 1;
    const suffix = n === 1 ? "st" : n === 2 ? "nd" : n === 3 ? "rd" : "th";
    return `${n}${suffix} Week`;
  });
}
