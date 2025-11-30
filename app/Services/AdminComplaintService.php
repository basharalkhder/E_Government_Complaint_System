<?php

namespace App\Services;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

class AdminComplaintService 
{

   


    // دالة مساعدة لإنشاء استعلام التصفية المشترك
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

    /**
     * جلب الشكاوى المفلترة (لـ index).
     */
    public function getFilteredComplaints(Request $request): Collection
    {
        return $this->applyFilters($request)
                    ->with(['user', 'entity'])
                    ->get();
    }

    /**
     * تصدير الشكاوى (لـ exportReports).
     */
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
    
    // --------------------------------------------------
    // دوال التصدير الداخلية
    // --------------------------------------------------

    private function exportToPdf(Collection $complaints)
    {
        $data = [
            'complaints' => $complaints,
            'title' => 'تقرير الشكاوى العام',
            'date' => now()->format('Y-m-d'),
            'total_count' => $complaints->count()
        ];

        // لاحظ أننا نستخدم الواجهة (Facade) داخل الـ Service
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
                $complaint->status,
                $complaint->entity->name_ar ?? 'غير معين',
                $complaint->user->name ?? 'غير معروف',
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
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // لدعم UTF-8 والعربية
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}