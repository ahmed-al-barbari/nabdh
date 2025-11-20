<?php

namespace App\Http\Controllers;

use App\Events\NewReportEvent;
use App\Models\Product;
use App\Models\Report;
use Auth;
use Exception;
use Illuminate\Http\Request;

class ReportController extends Controller {
    public function index() {
        return Report::with( 'product.store.user' )->paginate();
    }

    public function store( Product $product ) {
        try {
            $user = Auth::user();
            $report = $product->report()->updateOrCreate(
                [],
                [
                    'count' => \DB::raw( 'COALESCE(count, 0) + 1' ),
                    'status' => 'pending',
                ]
            );
            $num = $report->users()->syncWithoutDetaching( [ $user->id ] );
            if ( count( $num[ 'attached' ] ) ) {
                event( new NewReportEvent( $product, $user ) );
            }
        } catch ( Exception $e ) {
            throw $e;
        }
        return $report;
    }

    public function update( Request $request, Product $product ) {
        $report = $product->report;
        
        // Only increment if status is changing from pending to reviewed
        $wasPending = $report->status === 'pending';
        
        $report->update( [
            'status' => 'reviewed',
        ] );
        
        // When admin reviews a report, increment accurate_reports_count for all users who reported it
        if ( $wasPending ) {
            $report->users()->each( function ( $user ) {
                $user->increment( 'accurate_reports_count' );
            } );
        }
        
        return response()->json( [
            'message' => 'تم تحويل الحالة بنجاحّ'
        ] );
    }

    public function destroy( Product $product ) {
        $report = $product->report;
        
        if ( !$report ) {
            return response()->json( [
                'message' => 'البلاغ غير موجود'
            ], 404 );
        }
        
        // Delete the report (cascade will handle user_report pivot table)
        $report->delete();
        
        return response()->json( [
            'message' => 'تم حذف البلاغ بنجاح'
        ] );
    }
}
