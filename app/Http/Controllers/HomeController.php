<?php
namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ApiResource;
use App\Services\AnalyticsService;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class HomeController extends BaseController
{
    public function __construct(
        private readonly AuthService $authService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/analytics/dashboard",
     *     tags={"Analytics"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get analytics dashboard data",
     *     description="Fetch the main analytics dashboard data",
     *     @OA\Response(
     *         response=200,
     *         description="Data fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"status": true, "message": "Data fetched successfully", "data": {"users": 100, "sales": 2000}}
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=500, description="Server Error")
     * )
     */
    public function dashboard(): JsonResponse
    {
        $analyticsService = app(AnalyticsService::class);
        $data = $analyticsService->index();
        if (isset($data['error'])) {
            return $this->apiResource->error($data['error'], 400);
        }
        return $this->apiResource->success($data, "Data fetched successfully", 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/analytics/permissions",
     *     tags={"Analytics"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get permissions",
     *     description="Fetch analytics-related permissions",
     *     @OA\Response(
     *         response=200,
     *         description="Permissions fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"status": true, "message": "Permissions fetched successfully", "data": {"permissions": {"view_dashboard": true, "edit_settings": false}}}
     *         )
     *     )
     * )
     */
    public function permissions(): JsonResponse
    {
        try {
            $analyticsService = app(AnalyticsService::class);
            $result = $analyticsService->permissions();
            if (isset($result['error'])) {
                return $this->apiResource->error($result['error'], 400);
            }
            return $this->apiResource->success(['permissions' => (array) $result], "Permissions fetched successfully", 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/user",
     *     tags={"Analytics"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get analytics for a user",
     *     description="Fetch user-specific analytics data",
     *     @OA\Response(
     *         response=200,
     *         description="User data fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"status": true, "message": "Data fetched successfully", "data": {"id": 1, "name": "John Doe"}}
     *         )
     *     )
     * )
     */
    public function user(): JsonResponse
    {
        try {
            $analyticsService = app(AnalyticsService::class);
            $data = $analyticsService->showUser();
            if (isset($data['error'])) {
                return $this->apiResource->error($data['error'], 400);
            }
            return $this->apiResource->success($data, "Data fetched successfully", 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/analytics",
     *     tags={"Analytics"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get detailed dashboard analytics",
     *     @OA\Response(
     *         response=200,
     *         description="Analytics data fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"status": true, "message": "Data fetched successfully", "data": {"visitors": 300, "conversions": 25}}
     *         )
     *     )
     * )
     */
    public function dashboardAnalytics(): JsonResponse
    {
        try {
            $analyticsService = app(AnalyticsService::class);
            $data = $analyticsService->dashboardAnalytics();
            if (isset($data['error'])) {
                return $this->apiResource->error($data['error'], 400);
            }
            return $this->apiResource->success($data, "Data fetched successfully", 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/analytics/dashboard-projects",
     *     tags={"Analytics"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get project analytics",
     *     @OA\Response(
     *         response=200,
     *         description="Project analytics data",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"status": true, "message": "Data fetched successfully", "data": {"active_projects": 5, "completed_projects": 10}}
     *         )
     *     )
     * )
     */
    public function dashboardProjects(): JsonResponse
    {
        try {
            $analyticsService = app(AnalyticsService::class);
            $data = $analyticsService->dashboardProjects();
            if (isset($data['error'])) {
                return $this->apiResource->error($data['error'], 400);
            }
            return $this->apiResource->success($data, "Data fetched successfully", 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/analytics/dashboard-ecommerce",
     *     tags={"Analytics"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get e-commerce analytics",
     *     @OA\Response(
     *         response=200,
     *         description="E-commerce analytics data",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"status": true, "message": "Data fetched successfully", "data": {"orders": 50, "revenue": 12000}}
     *         )
     *     )
     * )
     */
    public function dashboarEcommerce(): JsonResponse
    {
        try {
            $analyticsService = app(AnalyticsService::class);
            $data = $analyticsService->dashboarEcommerce();
            if (isset($data['error'])) {
                return $this->apiResource->error($data['error'], 400);
            }
            return $this->apiResource->success($data, "Data fetched successfully", 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/analytics/dashboard-wallet",
     *     tags={"Analytics"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get wallet analytics",
     *     @OA\Response(
     *         response=200,
     *         description="Wallet analytics data",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"status": true, "message": "Data fetched successfully", "data": {"balance": 5000, "transactions": 20}}
     *         )
     *     )
     * )
     */
    public function dashboadWallet(): JsonResponse
    {
        try {
            $analyticsService = app(AnalyticsService::class);
            $data = $analyticsService->dashboadWallet();
            if (isset($data['error'])) {
                return $this->apiResource->error($data['error'], 400);
            }
            return $this->apiResource->success($data, "Data fetched successfully", 200);
        } catch (\Exception $e) {
            return $this->apiResource->error($e->getMessage(), 500);
        }
    }
}
