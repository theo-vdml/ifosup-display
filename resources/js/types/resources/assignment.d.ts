interface Assignment {
    date: string;
    period: 'morning' | 'afternoon' | 'evening';
    room: Room;
    course: Course;
}
