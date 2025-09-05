<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Custom;
use App\Models\Expense;
use App\Models\FAQ;
use App\Models\HomePage;
use App\Models\Loan;
use App\Models\NoticeBoard;
use App\Models\PackageTransaction;
use App\Models\Page;
use App\Models\Repayment;
use App\Models\Subscription;
use App\Models\Support;
use App\Models\User;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        if (\Auth::check()) {
            if (\Auth::user()->type == 'super admin') {
                $result['totalOrganization'] = User::where('type', 'owner')->count();
                $result['totalSubscription'] = Subscription::count();
                $result['totalTransaction'] = PackageTransaction::count();
                $result['totalIncome'] = PackageTransaction::sum('amount');
                $result['totalNote'] = NoticeBoard::where('parent_id', parentId())->count();
                $result['totalContact'] = Contact::where('parent_id', parentId())->count();

                $result['organizationByMonth'] = $this->organizationByMonth();
                $result['paymentByMonth'] = $this->paymentByMonth();

                return view('dashboard.super_admin', compact('result'));
            } else {

                if (\Auth::user()->type == 'customer') {
                    $id = \Auth::user()->id;

                    $loans = Loan::where('customer', $id)->get();
                    $pendingEMIs = [];
                    foreach ($loans as $loan) {
                        $firstPending = $loan->RepaymentSchedules->where('status', 'Pending')->sortBy('due_date')->first();
                        if ($firstPending) {
                            $pendingEMIs[] = $firstPending;
                        }
                    }
                    $result['loans'] = $pendingEMIs;

                    $pendingDetails = [];

                    foreach ($loans as $loan) {
                        $firstPaid = $loan->RepaymentSchedules->where('status', 'Paid')->sum('total_amount');
                        $firstPending = $loan->RepaymentSchedules->where('status', 'Pending')->sum('total_amount');

                        if ($firstPending) {
                            $pendingDetails[] = [
                                'loan' => $loan->loan_id,
                                'totalAmount' => $loan->amount,
                                'paid' => $firstPaid,
                                'pending' => $firstPending,
                                'total' => $firstPaid + $firstPending,
                            ];
                        }
                    }

                    $result['loanDetails'] = $pendingDetails;



                    return view('dashboard.customer', compact('result'));
                }

                $result['totalUser'] = User::where('parent_id', parentId())->count();
                $result['totalNote'] = NoticeBoard::where('parent_id', parentId())->count();
                $result['totalContact'] = Contact::where('parent_id', parentId())->count();
                $result['totalPendingLoan'] = Loan::where('parent_id', parentId())->where('status', 'under_review')->count();
                $result['totalActiveLoan'] = Loan::where('parent_id', parentId())->where('status', 'approved')->count();
                $result['totalExpense'] = Expense::where('parent_id', parentId())->sum('amount');
                //                               $result['totalExpense'] = Expense::where('parent_id', parentId())->sum('amount');
                $result['totalCustomer'] = User::where('type', 'customer')->where('parent_id', parentId())->count();
                //                $result['incomeExpenseByMonth'] = $this->incomeByMonth();
                $result['settings'] = settings();
                $result['paymentByMonth'] = $this->incomeByMonth();


                return view('dashboard.index', compact('result'));
            }
        } else {
            if (!file_exists(setup())) {
                header('location:install');
                die;
            } else {

                $landingPage = getSettingsValByName('landing_page');
                if ($landingPage == 'on') {
                    $subscriptions = Subscription::get();
                    $menus = Page::where('enabled', 1)->get();
                    $FAQs = FAQ::where('enabled', 1)->get();
                    return view('layouts.landing', compact('subscriptions', 'menus', 'FAQs'));
                } else {
                    return redirect()->route('login');
                }
            }
        }
    }
    public function organizationByMonth()
    {
        $start = strtotime(date('Y-01'));
        $end = strtotime(date('Y-12'));

        $currentdate = $start;

        $organization = [];
        while ($currentdate <= $end) {
            $organization['label'][] = date('M-Y', $currentdate);

            $month = date('m', $currentdate);
            $year = date('Y', $currentdate);
            $organization['data'][] = User::where('type', 'owner')->whereMonth('created_at', $month)->whereYear('created_at', $year)->count();
            $currentdate = strtotime('+1 month', $currentdate);
        }
        return $organization;
    }

    public function paymentByMonth()
    {
        $start = strtotime(date('Y-01'));
        $end = strtotime(date('Y-12'));
        $currentdate = $start;
        $payment = [];
        while ($currentdate <= $end) {
            $payment['label'][] = date('M-Y', $currentdate);
            $month = date('m', $currentdate);
            $year = date('Y', $currentdate);
            $payment['data'][] = PackageTransaction::whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('amount');
            $currentdate = strtotime('+1 month', $currentdate);
        }
        return $payment;
    }

    public function incomeByMonth()
    {
        $start = strtotime(date('Y-01'));
        $end = strtotime(date('Y-12'));
        $currentdate = $start;
        $payment = [];
        while ($currentdate <= $end) {
            $payment['label'][] = date('M-Y', $currentdate);
            $month = date('m', $currentdate);
            $year = date('Y', $currentdate);
            $payment['repayment'][] = Repayment::where('parent_id', parentId())->whereMonth('payment_date', $month)->whereYear('payment_date', $year)->sum('total_amount');
            $currentdate = strtotime('+1 month', $currentdate);
        }
        return $payment;
    }
}
