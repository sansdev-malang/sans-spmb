<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Registration;
use App\Models\Payment;

#[Signature('app:test-spmb-api')]
#[Description('Runs integration tests on the SPMB RESTful APIs and Winpay simulation')]
class TestSpmbApi extends Command
{
    protected $token;
    protected $userId;
    protected $registrationId;
    protected $invoiceNumber;

    public function handle()
    {
        $this->info('Starting SPMB API & Winpay Integration Test...');
        
        // Clean up database and storage
        $this->cleanUp();

        // 1. Test Register
        $this->testRegister();

        // 2. Test Login
        $this->testLogin();

        // 3. Test Registration - Step 1: Candidate Info
        $this->testCandidateInfo();

        // 4. Test Registration - Step 2: Parent Info
        $this->testParentInfo();

        // 5. Test Registration - Step 3: Documents Upload
        $this->testDocumentsUpload();

        // 6. Test Payment - Charge Initiation (VA)
        $this->testPaymentCharge('MANDIRI');

        // 7. Test Payment - Callback webhook simulation
        $this->testPaymentCallback();

        // 8. Test Dashboard Status (Final Verification check)
        $this->testDashboard();

        $this->info('All integration tests passed successfully!');
    }

    protected function cleanUp()
    {
        User::where('email', 'test@example.com')->delete();
        Storage::fake('public');
    }

    protected function callApi($uri, $method = 'GET', $data = [], $headers = [])
    {
        $content = null;
        $parameters = [];
        
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            $content = json_encode($data);
            $headers['Content-Type'] = 'application/json';
        } else {
            $parameters = $data;
        }

        $request = Request::create($uri, $method, $parameters, [], [], [], $content);
        
        // Apply Sanctum token if present
        if ($this->token) {
            $request->headers->set('Authorization', 'Bearer ' . $this->token);
        }

        foreach ($headers as $key => $val) {
            $request->headers->set($key, $val);
        }

        // Set JSON headers
        $request->headers->set('Accept', 'application/json');

        $response = app()->handle($request);
        return json_decode($response->getContent(), true);
    }

    protected function testRegister()
    {
        $this->comment('Testing Register API...');
        
        $response = $this->callApi('/api/register', 'POST', [
            'name' => 'Ahmad Raihan',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        if (isset($response['access_token'])) {
            $this->token = $response['access_token'];
            $this->userId = $response['user']['id'];
            $this->registrationId = $response['registration_id'];
            $this->info('Register Success! Token: ' . substr($this->token, 0, 15) . '...');
        } else {
            $this->error('Register failed: ' . json_encode($response));
            exit(1);
        }
    }

    protected function testLogin()
    {
        $this->comment('Testing Login API...');
        
        // Reset token to ensure login generates a fresh one
        $this->token = null;

        $response = $this->callApi('/api/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        if (isset($response['access_token'])) {
            $this->token = $response['access_token'];
            $this->info('Login Success! Fresh Token acquired.');
        } else {
            $this->error('Login failed: ' . json_encode($response));
            exit(1);
        }
    }

    protected function testCandidateInfo()
    {
        $this->comment('Testing Candidate Info API (Step 1)...');

        $response = $this->callApi('/api/registration/candidate-info', 'POST', [
            'candidate_name' => 'Ahmad Raihan',
            'nickname' => 'Raihan',
            'nik' => '1234567890123456',
            'gender' => 'male',
            'birth_place' => 'Bandung',
            'birth_date' => '2018-05-15',
            'religion' => 'Islam',
            'previous_school' => 'TK Aisyiyah',
            'admission_level' => 'TK B',
        ]);

        if (isset($response['registration']['candidate_name']) && $response['registration']['candidate_name'] === 'Ahmad Raihan') {
            $this->info('Candidate Info Step 1 saved.');
        } else {
            $this->error('Step 1 failed: ' . json_encode($response));
            exit(1);
        }
    }

    protected function testParentInfo()
    {
        $this->comment('Testing Parent Info API (Step 2)...');

        $response = $this->callApi('/api/registration/parent-info', 'POST', [
            'father_name' => 'Budi Santoso',
            'mother_name' => 'Siti Aminah',
            'parent_phone' => '081234567890',
        ]);

        if (isset($response['registration']['father_name']) && $response['registration']['father_name'] === 'Budi Santoso') {
            $this->info('Parent Info Step 2 saved.');
        } else {
            $this->error('Step 2 failed: ' . json_encode($response));
            exit(1);
        }
    }

    protected function testDocumentsUpload()
    {
        $this->comment('Testing Documents Upload API (Step 3)...');

        // Create mock files
        $birthCert = UploadedFile::fake()->create('birth_certificate.pdf', 500, 'application/pdf');
        $familyCard = UploadedFile::fake()->image('family_card.jpg');

        $request = Request::create('/api/registration/documents', 'POST', [], [], [
            'birth_certificate' => $birthCert,
            'family_card' => $familyCard,
        ]);
        $request->headers->set('Authorization', 'Bearer ' . $this->token);
        $request->headers->set('Accept', 'application/json');

        $response = app()->handle($request);
        $data = json_decode($response->getContent(), true);

        if (isset($data['registration']['registration_status']) && $data['registration']['registration_status'] === 'submitted') {
            $this->info('Documents uploaded & Registration status transitioned to "submitted".');
        } else {
            $this->error('Step 3 Upload failed: ' . $response->getContent());
            exit(1);
        }
    }

    protected function testPaymentCharge($method)
    {
        $this->comment("Testing Payment Charge API (Method: {$method})...");

        $response = $this->callApi('/api/payments/charge', 'POST', [
            'payment_method' => $method
        ]);

        if (isset($response['payment']['invoice_number'])) {
            $this->invoiceNumber = $response['payment']['invoice_number'];
            $this->info('Payment Charge initiated! Invoice: ' . $this->invoiceNumber);
            $this->info('VA Info: ' . json_encode($response['payment']['payment_info']));
        } else {
            $this->error('Payment Charge initiation failed: ' . json_encode($response));
            exit(1);
        }
    }

    protected function testPaymentCallback()
    {
        $this->comment('Testing Webhook Callback Simulation from Winpay...');

        // Winpay SNAP notification body
        $callbackBody = [
            'trxId' => $this->invoiceNumber,
            'paymentStatus' => 'SUCCESS',
            'paymentAmount' => [
                'value' => '350000.00',
                'currency' => 'IDR'
            ],
            'virtualAccountNo' => '88990012345678',
            'paymentRequestId' => 'PAY-MOCK-TEST'
        ];

        // Call callback public API
        $response = $this->callApi('/api/payments/callback', 'POST', $callbackBody);

        if (isset($response['responseCode']) && $response['responseCode'] === '2002500') {
            $this->info('Callback parsed successfully. Status updated.');
        } else {
            $this->error('Callback failed: ' . json_encode($response));
            exit(1);
        }
    }

    protected function testDashboard()
    {
        $this->comment('Testing Dashboard Status API...');

        $response = $this->callApi('/api/dashboard', 'GET');

        if (isset($response['payment_status']) && $response['payment_status'] === 'paid') {
            $this->info('Dashboard shows payment status is successfully updated to: PAID.');
            $this->info('Committee Message: ' . $response['committee_message']);
            $this->info('Registration step status: ' . $response['timeline']['registration']['status']);
            $this->info('Payment step status: ' . $response['timeline']['payment']['status']);
            $this->info('Verification step status: ' . $response['timeline']['verification']['status']);
            
            // Test Admin API protection (should fail for candidate)
            $this->comment('Testing Admin API protection...');
            $failResponse = $this->callApi('/api/admin/registrations');
            if (isset($failResponse['message']) && str_contains($failResponse['message'], 'Admin access required')) {
                $this->info('Admin API protection verified. Candidate was successfully blocked.');
            } else {
                $this->error('Admin API protection failed: Candidate was not blocked. Response: ' . json_encode($failResponse));
                exit(1);
            }

            // Temporarily set user to admin to pass AdminMiddleware
            User::find($this->userId)->update(['role' => 'admin']);
            auth()->forgetUser();

            // Test Admin API: List registrations
            $this->comment('Testing Admin API: List registrations...');
            $adminList = $this->callApi('/api/admin/registrations');
            if (isset($adminList['registrations']['data']) && count($adminList['registrations']['data']) > 0) {
                $this->info('Admin List registrations success. Found: ' . count($adminList['registrations']['data']) . ' candidate(s).');
            } else {
                $this->error('Admin List registrations failed: ' . json_encode($adminList));
                exit(1);
            }

            // Test Admin API: Detail registration
            $this->comment('Testing Admin API: Registration Detail...');
            $adminDetail = $this->callApi('/api/admin/registrations/' . $this->registrationId);
            if (isset($adminDetail['registration']['id'])) {
                $this->info('Admin Registration Detail success.');
            } else {
                $this->error('Admin Registration Detail failed: ' . json_encode($adminDetail));
                exit(1);
            }

            // Test Admin API: Verify registration
            $this->comment('Testing Admin API: Verify registration...');
            $verifyResponse = $this->callApi('/api/admin/registrations/' . $this->registrationId . '/verify', 'POST', [
                'notes' => 'Alhamdulillah, berkas pendaftaran ananda Ahmad Raihan terverifikasi. Silakan persiapkan untuk mengikuti Tes Observasi.'
            ]);

            if (isset($verifyResponse['registration']['registration_status']) && $verifyResponse['registration']['registration_status'] === 'verified') {
                $this->info('Admin verified registration successfully.');
            } else {
                $this->error('Admin verification failed: ' . json_encode($verifyResponse));
                exit(1);
            }

            // Restore user role back to candidate
            User::find($this->userId)->update(['role' => 'candidate']);
            auth()->forgetUser();

            // Re-checking candidate dashboard after admin verification
            $this->comment('Re-checking candidate Dashboard after Admin verification...');
            $responseVerified = $this->callApi('/api/dashboard', 'GET');

            $this->info('Updated Committee Message: ' . $responseVerified['committee_message']);
            $this->info('Observation schedule found: ' . json_encode($responseVerified['observation_details']));
        } else {
            $this->error('Dashboard status verification failed: ' . json_encode($response));
            exit(1);
        }
    }
}
