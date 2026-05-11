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
    recurring_assignment_id?: number;
    is_detached: boolean;
}

type AssignmentWithRelations = Assignment & {
    course: Course;
    room?: Room;
};

interface RecurringAssignment {
    id: number;
    course_id: number;
    course?: Course;
    room_id: number;
    room?: Room;
    period: AssignmentPeriod;
    day_of_week: number;
    start_week: string;
    end_week: string;
}

type RecurringAssignmentWithRelations = RecurringAssignment & {
    course: Course;
    room: Room;
};
