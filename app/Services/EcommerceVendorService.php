<?php
namespace App\Services;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;

class EcommerceVendorService extends BaseService
{
    //

    public  function __construct(
        private $ecommerce,
        StoreFileResource $storeFileResource,
        ResponseHelper $responseHelper
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }



    public function ecommerceData(array $requestData)
    {
        try {
             $ecommerce = $this->ecommerce($requestData);
        if (! $ecommerce) {
            return $this->responseHelper->error('You do not have an ecommerce site', ["result" => $ecommerce], 403);
        }

        $ecommerce           = $this->ecommerce($requestData);
        $eccommerce_settings = $ecommerce->ecommerceSetting;
        $products            = $ecommerce->products;
        $profile             = $ecommerce->profile;
        $saleSetting         = $ecommerce->saleSetting;

        return $this->responseHelper->respond(["ecommerce" => $ecommerce,
            "ecommerce_settings"                    => $eccommerce_settings,
            "products"                              => $products,
            "profile"                               => $profile,
            "saleSetting"                           => $saleSetting,
        ], 'Update successful');
        } catch (\Exception $e) {
            return $this->responseHelper->error('Validation Error', ['error' => $e->getMessage()], 422);
        }


    }

}
