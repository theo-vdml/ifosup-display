interface Assignment {
    date: string;
    period: 'morning' | 'afternoon' | 'evening';
    course_id: number;
    course?: Course;
    room_id?: number;
    room?: Room;
}
