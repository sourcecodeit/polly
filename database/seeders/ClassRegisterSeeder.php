<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Lesson;
use App\Models\School;
use App\Models\Student;
use App\Models\Vote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ClassRegisterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a school
        $school = School::create([
            'name' => 'Springfield Elementary School',
            'address' => '123 Main Street',
            'city' => 'Springfield',
            'phone' => '555-1234',
            'email' => 'info@springfield.edu',
        ]);

        // Create classes
        $class1 = ClassRoom::create([
            'school_id' => $school->id,
            'name' => 'Class 4A',
            'year' => '2025',
            'section' => 'A',
            'description' => 'Fourth grade, section A',
        ]);

        $class2 = ClassRoom::create([
            'school_id' => $school->id,
            'name' => 'Class 5B',
            'year' => '2025',
            'section' => 'B',
            'description' => 'Fifth grade, section B',
        ]);

        // Create students for Class 4A
        $students1 = [
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'birth_date' => '2015-05-10',
                'gender' => 'male',
            ],
            [
                'first_name' => 'Emily',
                'last_name' => 'Johnson',
                'birth_date' => '2015-07-22',
                'gender' => 'female',
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Brown',
                'birth_date' => '2015-03-15',
                'gender' => 'male',
            ],
            [
                'first_name' => 'Sophia',
                'last_name' => 'Davis',
                'birth_date' => '2015-11-30',
                'gender' => 'female',
            ],
        ];

        foreach ($students1 as $studentData) {
            $student = new Student($studentData);
            $student->class_id = $class1->id;
            $student->save();
        }

        // Create students for Class 5B
        $students2 = [
            [
                'first_name' => 'William',
                'last_name' => 'Wilson',
                'birth_date' => '2014-02-18',
                'gender' => 'male',
            ],
            [
                'first_name' => 'Olivia',
                'last_name' => 'Martinez',
                'birth_date' => '2014-09-05',
                'gender' => 'female',
            ],
            [
                'first_name' => 'James',
                'last_name' => 'Anderson',
                'birth_date' => '2014-06-12',
                'gender' => 'male',
            ],
            [
                'first_name' => 'Emma',
                'last_name' => 'Taylor',
                'birth_date' => '2014-12-25',
                'gender' => 'female',
            ],
        ];

        foreach ($students2 as $studentData) {
            $student = new Student($studentData);
            $student->class_id = $class2->id;
            $student->save();
        }

        // Create lessons for Class 4A
        $lessons1 = [
            [
                'subject' => 'Mathematics',
                'start_time' => Carbon::today()->setHour(9)->setMinute(0),
                'end_time' => Carbon::today()->setHour(10)->setMinute(0),
                'teacher_name' => 'Mrs. Johnson',
            ],
            [
                'subject' => 'English',
                'start_time' => Carbon::today()->setHour(10)->setMinute(30),
                'end_time' => Carbon::today()->setHour(11)->setMinute(30),
                'teacher_name' => 'Mr. Davis',
            ],
            [
                'subject' => 'Science',
                'start_time' => Carbon::tomorrow()->setHour(9)->setMinute(0),
                'end_time' => Carbon::tomorrow()->setHour(10)->setMinute(0),
                'teacher_name' => 'Mrs. Wilson',
            ],
        ];

        foreach ($lessons1 as $lessonData) {
            $lesson = new Lesson($lessonData);
            $lesson->class_id = $class1->id;
            $lesson->save();

            // Create attendances for this lesson
            $students = Student::where('class_id', $class1->id)->get();
            foreach ($students as $student) {
                Attendance::create([
                    'lesson_id' => $lesson->id,
                    'student_id' => $student->id,
                    'status' => array_rand(['present' => 0, 'absent' => 1, 'late' => 2], 1),
                ]);
            }
        }

        // Create lessons for Class 5B
        $lessons2 = [
            [
                'subject' => 'History',
                'start_time' => Carbon::today()->setHour(9)->setMinute(0),
                'end_time' => Carbon::today()->setHour(10)->setMinute(0),
                'teacher_name' => 'Mr. Brown',
            ],
            [
                'subject' => 'Art',
                'start_time' => Carbon::today()->setHour(10)->setMinute(30),
                'end_time' => Carbon::today()->setHour(11)->setMinute(30),
                'teacher_name' => 'Mrs. Smith',
            ],
            [
                'subject' => 'Physical Education',
                'start_time' => Carbon::tomorrow()->setHour(9)->setMinute(0),
                'end_time' => Carbon::tomorrow()->setHour(10)->setMinute(0),
                'teacher_name' => 'Mr. Johnson',
            ],
        ];

        foreach ($lessons2 as $lessonData) {
            $lesson = new Lesson($lessonData);
            $lesson->class_id = $class2->id;
            $lesson->save();

            // Create attendances for this lesson
            $students = Student::where('class_id', $class2->id)->get();
            foreach ($students as $student) {
                Attendance::create([
                    'lesson_id' => $lesson->id,
                    'student_id' => $student->id,
                    'status' => array_rand(['present' => 0, 'absent' => 1, 'late' => 2], 1),
                ]);
            }
        }

        // Create votes for students
        $students = Student::all();
        $subjects = ['Mathematics', 'English', 'Science', 'History', 'Art', 'Physical Education'];

        foreach ($students as $student) {
            for ($i = 0; $i < 3; $i++) {
                Vote::create([
                    'student_id' => $student->id,
                    'vote_date' => Carbon::today()->subDays(rand(0, 30)),
                    'value' => rand(60, 100) / 10,
                    'subject' => $subjects[array_rand($subjects)],
                    'description' => 'Regular test',
                ]);
            }
        }
    }
}
