<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Services\Reporting\ReportRegistry;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private readonly ReportRegistry $reports) {}

    public function index(Request $request): View
    {
        return view('core::reports.index', [
            'reports' => $this->reports->availableFor($request->user()),
        ]);
    }

    public function download(Request $request, string $key): StreamedResponse
    {
        $report = $this->reports->get($key);

        abort_if($report === null, 404);
        abort_if(
            $report['permission'] !== null && ! $request->user()->hasPermission($report['permission']),
            403,
        );

        $user = $request->user();
        $filename = str_replace('.', '-', $key).'-'.now()->format('Ymd-Hi').'.csv';

        return response()->streamDownload(function () use ($report, $user) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\u{FEFF}"); // BOM so Excel opens UTF-8 correctly
            fputcsv($out, $report['headers']);

            foreach (($report['rows'])($user) as $row) {
                fputcsv($out, $row);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
