type AssignmentPeriod = 'morning' | 'afternoon' | 'evening';

interface Assignment {
    date: string;
    period: AssignmentPeriod;
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
    start_date: string;
    end_date: string;
}

type RecurringAssignmentWithRelations = RecurringAssignment & {
    course: Course;
    room: Room;
};
