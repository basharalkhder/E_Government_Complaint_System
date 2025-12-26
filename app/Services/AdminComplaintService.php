<?php

namespace App\Services;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use App\Models\Entity;
use App\Enums\ComplaintStatus;
use App\Models\ComplaintHistory;
use Spatie\Activitylog\Models\Activity;

class AdminComplaintService
{

    public function getAllComplaintsForAdmin()
    {
        return Complaint::with([
            'user:id,name,email',
            'entity',
            'attachments'
        ])->paginate(15);
    }


    private function applyFilters(Request $request)
    {
        $query = Complaint::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('entity_id')) {
            $query->where('entity_id', $request->entity_id);
        }

        return $query;
    }




    public function exportComplaints(Request $request)
    {
        $complaints = $this->applyFilters($request)
            ->with(['user', 'entity'])
            ->get();

        $format = strtolower($request->get('format', 'csv'));

        if ($format === 'pdf') {
            return $this->exportToPdf($complaints);
        }

        return $this->exportToCsv($complaints);
    }


    private function exportToPdf(Collection $complaints)
    {
        $data = [
            'complaints' => $complaints,
            'title' => 'General Complaints Report',
            'date' => now()->format('Y-m-d'),
            'total_count' => $complaints->count()
        ];


        $pdf = Pdf::loadView('admin.reports.complaint_pdf', $data);
        $pdf->setPaper('a4', 'landscape');
        $fileName = 'complaint_report_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    }

    private function exportToCsv(Collection $complaints): StreamedResponse
    {
        $csvData[] = ['ID', 'Reference', 'Status', 'Entity Name (AR)', 'Complainant Name', 'Submission Date', 'Description'];

        foreach ($complaints as $complaint) {
            $csvData[] = [
                $complaint->id,
                $complaint->reference_number,
                $complaint->status->value,
                $complaint->entity->name_ar ?? 'undefined',
                $complaint->user->name ?? ' undefined',
                $complaint->created_at->format('Y-m-d H:i:s'),
                str_replace(["\r", "\n", ","], " ", $complaint->description),
            ];
        }

        $fileName = 'complaint_report_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($csvData) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }


    public function getComplaintsStatistics()
    {
        return [
            // العدد الكلي للشكاوى
            'total_complaints' => Complaint::count(),

            // تصنيف حسب الحالة (باستخدام الـ Enum الخاص بك)
            'new_complaints' => Complaint::where('status', ComplaintStatus::NEW->value)->count(),
            'in_progress_complaints' => Complaint::where('status', ComplaintStatus::IN_PROGRESS->value)->count(),
            'resolved_complaints' => Complaint::where('status', ComplaintStatus::RESOLVED->value)->count(),
            'rejected_complaints' => Complaint::where('status', ComplaintStatus::REJECTED->value)->count(),

            // إحصائية إضافية: أكثر جهة عليها شكاوى
            'most_complained_entity' => Entity::select('id', 'name_ar')->withCount('complaints')
                ->orderBy('complaints_count', 'desc')
                ->first()
                ?->only(['id', 'name_ar', 'complaints_count']),

            // إحصائية الشكاوى في آخر 24 ساعة
            'recent_24h_count' => Complaint::where('created_at', '>=', now()->subDay())->count(),
        ];
    }


    public function getComplaintHistoryByComplaintId($complaintId)
    {

        return ComplaintHistory::where('complaint_id', $complaintId)
            ->with('user:id,name,email')
            ->latest()
            ->get();
    }







    public function getAllTraces()
    {
        return Activity::with('causer') // جلب هوية المستخدم
            ->latest()
            ->paginate(15);
    }

    
    public function getSubjectUrl($log)
    {
        
        if (!$log->subject_id) return null;

        
        $path = match ($log->subject_type) {
            'App\Models\User'      => 'users',
            'App\Models\Entity'    => 'entities',
            'App\Models\Complaint' => 'all-complaints', // حسب الاسم الموجود عندك في api.php
            'App\Models\Role'      => 'roles',
            default                => null,
        };

        if ($path) {
            // بناء الرابط يدوياً: domain.com/api/path/id
            return url("/api/{$path}/{$log->subject_id}");
        }

        return null;
    }
}
