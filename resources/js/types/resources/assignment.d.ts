type AssignmentPeriod = 'morning' | 'afternoon' | 'evening';
type AssignmentStatus = 'planned' | 'cancelled' | 'late';

interface Assignment {
    date: string;
    period: AssignmentPeriod;
    status: AssignmentStatus;
    course_id: number;
    course?: Course;
    room_id?: number;
    room?: Room;
}

type AssignmentWithRelations = Assignment & {
    course: Course;
    room?: Room;
};
