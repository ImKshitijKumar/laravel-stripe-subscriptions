<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        return view('login');
    }

    public function login_auth(Request $req)
    {
        $email = $req->post('email');
        $password = $req->post('password');

        $res = Admin::where(['email' => $email, 'password' => $password])->get();

        if (isset($res[0]->id)) {
            $req->session()->put('IS_LOGIN', true);
            $req->session()->put('ADMIN_ID', $res[0]->id);
            return redirect('dashboard');
        } else {
            $req->session()->flash('error', 'Invalid credentials!!');
            return redirect('/');
        }
    }

    public function dashboard(Request $req)
    {
        // $date = date("Y-m-01");
        // $newdate = strtotime ( '+1 month', 1621004064 );
        // echo $newdate;
        $stripePublishKey = $req->post('stripe_publish_key');
        $stripeSecretKey = $req->post('stripe_secret_key');

        try {
            if ($stripeSecretKey != "") {
                $stripe = new \Stripe\StripeClient($stripeSecretKey);
                $result = $stripe->subscriptions->all(['status' => 'active']);
                
                if ($result) {
                    $subscriptionList['data'] = $result->data;
                    return view('dashboard', $subscriptionList);
                } else {
                    throw new \Exception("Invalid Key");
                }
            } else {
                $req->session()->flash('error', 'Please Provide Stripe Secrect Key');
                return view('dashboard');
            }
        } catch (\Throwable $th) {
            $req->session()->flash('error', $th->getMessage());
            return view('dashboard');
        }

        // echo "<pre>";
        // print_r($subscriptionList);
    }

    public function export(Request $req)
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

        $result = $stripe->subscriptions->all(['status' => 'active']);

        $subscriptionList = $result->data;
        try {
            if (empty($subscriptionList)) {
                throw new \Exception("No Data Available for Exporting");
            } else {
                $headers = array(
                    "Content-type" => "text/csv",
                    "Content-Disposition" => "attachment; filename=subscriptions.csv",
                    "Pragma" => "no-cache",
                    "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                    "Expires" => "0"
                );

                $columns = array(
                    'Customer ID',
                    'Created At',
                    'Renewal Date',
                    'Amount',
                );

                $callback = function () use ($subscriptionList, $columns) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);
                    $date = date("m");
                    $newdate = strtotime ( '+1 month');
                    $newMonth = date('m', $newdate);
                    $total = 0;

                    foreach ($subscriptionList as $list) {
                        if ($newMonth == date('m', $list->current_period_end)) {
                            $total = $total + ($list->items->data[0]->plan->amount/100);
                            fputcsv($file, array(
                                'Customer ID' => $list->customer,
                                'Created At' => date('d-m-Y', $list->created),
                                'Renewal Date' => date('d-m-Y', $list->current_period_end),
                                'Amount' => $list->items->data[0]->plan->amount/100
                            ));
                        }
                    }
                    fputcsv($file, array(
                        "Customer ID" => "",
                        "Created At" => "",
                        "Renewal Date" => "",
                        'Amount' => 'Total = '.$total
                    ));
                    fclose($file);
                };
                return response()->stream($callback, 200, $headers);
            }
        } catch (\Throwable $th) {
            $req->session()->flash('error', $th->getMessage());
            return ('/');
        }
    }
}
// 'current_period_start.gt' => 1621004064