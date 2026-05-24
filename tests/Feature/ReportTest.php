<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\SchoolData;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        SchoolData::factory()->create([
            'name' => 'SMK Globin',
            'headmaster_name' => 'Dr. H. Ahmad Fauzi, M.Pd.',
        ]);

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
    }

    public function test_index_displays_reports_page(): void
    {
        ClassRoom::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('reports'));

        $response->assertOk();
    }

    public function test_export_excel(): void
    {
        Student::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('reports.excel'));

        $response->assertOk();
        $response->assertHeader('Content-Disposition');
    }

    public function test_export_pdf(): void
    {
        Student::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('reports.pdf'));

        $response->assertOk();
        $response->assertHeader('Content-Disposition');
    }

    public function test_export_pdf_with_filters(): void
    {
        $class = ClassRoom::factory()->create();
        Student::factory()->count(2)->create(['class_id' => $class->id]);

        $response = $this->actingAs($this->user)->get(route('reports.pdf', [
            'class_id' => $class->id,
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Disposition');
    }

    public function test_buku_tabungan(): void
    {
        $student = Student::factory()->create();
        Transaction::factory()->count(2)->create([
            'student_id' => $student->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.buku-tabungan', $student));

        $response->assertOk();
        $response->assertHeader('Content-Disposition');
    }

    public function test_buku_tabungan_validates_student_exists(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.buku-tabungan', 99999));

        $response->assertNotFound();
    }
}
