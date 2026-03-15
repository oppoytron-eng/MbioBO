<?php

namespace Tests\Feature;

use App\Models\ApiDriverDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_routes_flow()
    {
        $clientPayload = [
            'name' => 'Client Route Tester',
            'telephone' => '0620000001',
            'email' => 'client@test.local',
            'password' => 'secret123',
            'role' => 'client',
        ];

        $clientResponse = $this->postJson('api/register', $clientPayload);
        $clientResponse->assertCreated()
            ->assertJsonStructure(['message', 'token', 'user']);

        $clientToken = $clientResponse->json('token');

        $driver = User::factory()->chauffeur()->create();
        $this->assertSame('chauffeur', $driver->role);

        foreach (['permis', 'assurance', 'carte_grise'] as $type) {
            ApiDriverDocument::create([
                'chauffeur_id' => $driver->id,
                'type' => $type,
                'status' => 'approved',
                'path' => "documents/{$type}.pdf",
            ]);
        }

        $this->withHeader('Authorization', "Bearer $clientToken")->getJson('api/user')
            ->assertOk()
            ->assertJsonPath('email', $clientPayload['email']);

        $courseResponse = $this->withHeader('Authorization', "Bearer $clientToken")->postJson('api/courses', [
            'depart_latitude' => 14.686,
            'depart_longitude' => -17.440,
            'arrivee_latitude' => 14.690,
            'arrivee_longitude' => -17.450,
            'prix_estime' => 1250,
        ]);

        $courseResponse->assertCreated()->assertJsonPath('course.statut', 'en_attente');

        $coursePayload = $courseResponse->json('course');
        $courseId = $coursePayload['id'];
        $rideOtp = $coursePayload['ride_otp'];

        $this->withHeader('Authorization', "Bearer $clientToken")->getJson('api/client/profil')
            ->assertOk()
            ->assertJsonPath('email', $clientPayload['email']);

        $this->withHeader('Authorization', "Bearer $clientToken")->putJson('api/client/profil', [
            'name' => 'Client Updated',
            'telephone' => '0620000003',
        ])->assertOk()->assertJsonPath('user.name', 'Client Updated');

        $this->withHeader('Authorization', "Bearer $clientToken")->getJson('api/courses/mes-courses')
            ->assertOk()
            ->assertJsonCount(1);

        $this->withHeader('Authorization', "Bearer $clientToken")->getJson("api/courses/{$courseId}")
            ->assertOk()
            ->assertJsonPath('id', $courseId);

        $this->withHeader('Authorization', "Bearer $clientToken")->postJson("api/notifications/notifier/{$courseId}")
            ->assertOk()
            ->assertJsonStructure(['message', 'notification', 'nb_chauffeurs']);

        $this->withHeader('Authorization', "Bearer $clientToken")->postJson('api/logout')->assertOk();

        Sanctum::actingAs($driver);

        $this->getJson('api/wallet')
            ->assertOk()
            ->assertJsonStructure([
                'wallet' => ['id', 'user_id', 'balance'],
                'pending_payouts',
            ])
            ->assertJsonPath('wallet.user_id', $driver->id);

        $this->getJson('api/user')
            ->assertOk()
            ->assertJsonPath('role', 'chauffeur');

        $this->getJson('api/courses/disponibles')
            ->assertOk()
            ->assertJsonFragment(['id' => $courseId]);

        $this->postJson("api/courses/{$courseId}/accepter")
            ->assertOk()
            ->assertJsonPath('course.statut', 'acceptee');

        $this->postJson("api/courses/{$courseId}/otp-verify", [
            'otp' => $rideOtp,
        ])->assertOk()->assertJsonPath('course.statut', 'acceptee');

        $this->postJson("api/courses/{$courseId}/demarrer")
            ->assertOk()
            ->assertJsonPath('course.statut', 'en_cours');

        $this->postJson("api/courses/{$courseId}/terminer", [
            'prix_final' => 1500,
        ])->assertOk()->assertJsonPath('course.statut', 'terminee');

        $this->postJson("api/courses/{$courseId}/annuler")
            ->assertOk()
            ->assertJsonPath('course.statut', 'annulee');

        $notifications = $this->getJson('api/notifications/mes-notifications')
            ->assertOk()
            ->assertJsonCount(1);

        $notificationId = $notifications->json('0.id');

        $this->putJson("api/notifications/{$notificationId}/vue")
            ->assertOk();
    }

    public function test_login_route_returns_token()
    {
        $payload = [
            'name' => 'Login Tester',
            'telephone' => '0620000004',
            'email' => 'login@test.local',
            'password' => 'secret123',
            'role' => 'client',
        ];

        $this->postJson('api/register', $payload)
            ->assertCreated();

        $this->postJson('api/login', [
            'email' => $payload['email'],
            'password' => $payload['password'],
        ])->assertOk()
            ->assertJsonStructure(['message', 'token', 'user']);
    }
}
