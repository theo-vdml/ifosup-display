interface Course {
    id: number;
    name: string;
    code: string;
    teacher_id: number;
    teacher?: Teacher;
    groups?: Group[];
}
