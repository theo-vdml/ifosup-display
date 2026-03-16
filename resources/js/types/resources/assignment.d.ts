type AssignmentPeriod = 'morning' | 'afternoon' | 'evening';

interface Assignment {
    date: string;
    period: AssignmentPeriod;
    course_id: number;
    course?: Course;
    room_id?: number;
    room?: Room;
}
