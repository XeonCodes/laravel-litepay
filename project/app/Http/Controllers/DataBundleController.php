<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataBundleController extends Controller
{

    public function DataPlans () {

        $data = [
            "mtn" => [
                [
                    "ID" => "01",
                    "PRODUCT" => [
                        [
                            "PRODUCT_CODE" => "2",
                            "PRODUCT_ID" => "411",
                            "PRODUCT_NAME" => "500MB - 7 Days (SME)",
                            "PRODUCT_AMOUNT" => 420,
                            "PRODUCT_SOURCE" => 1
                        ],
                        [
                            "PRODUCT_CODE" => "200",
                            "PRODUCT_ID" => "412",
                            "PRODUCT_NAME" => "1GB - 7 Days (SME)",
                            "PRODUCT_AMOUNT" => 590,
                            "PRODUCT_SOURCE" => 1
                        ],
                        [
                            "PRODUCT_CODE" => "3",
                            "PRODUCT_ID" => "413",
                            "PRODUCT_NAME" => "2GB - 7 Days (SME)",
                            "PRODUCT_AMOUNT" => 1200,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "4",
                            "PRODUCT_ID" => "215",
                            "PRODUCT_NAME" => "1 GB - 1 day (Gifting)",
                            "PRODUCT_AMOUNT" => 485,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "5",
                            "PRODUCT_ID" => "364",
                            "PRODUCT_NAME" => "1.5 GB - 2 days (GIFTING)",
                            "PRODUCT_AMOUNT" => 582,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "5",
                            "PRODUCT_ID" => "365",
                            "PRODUCT_NAME" => "1.5 GB - 7 days (GIFTING)",
                            "PRODUCT_AMOUNT" => 970,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "5",
                            "PRODUCT_ID" => "414",
                            "PRODUCT_NAME" => "3GB - 30 Days (SME)",
                            "PRODUCT_AMOUNT" => 1830,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "5",
                            "PRODUCT_ID" => "362",
                            "PRODUCT_NAME" => "1.8GB + 35 min call+150SMS - 30 Days (Gifting)",
                            "PRODUCT_AMOUNT" => 1455,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "6",
                            "PRODUCT_ID" => "216",
                            "PRODUCT_NAME" => "3.2 GB - 2 days (Gifting)",
                            "PRODUCT_AMOUNT" => 980,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "6",
                            "PRODUCT_ID" => "318",
                            "PRODUCT_NAME" => "2 GB - 2 days (Gifting)",
                            "PRODUCT_AMOUNT" => 727.5,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "7",
                            "PRODUCT_ID" => "415",
                            "PRODUCT_NAME" => "5GB - 30 Days (SME)",
                            "PRODUCT_AMOUNT" => 3050,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "8",
                            "PRODUCT_ID" => "217",
                            "PRODUCT_NAME" => "6GB - 7 days (Gifting)",
                            "PRODUCT_AMOUNT" => 2425,
                            "PRODUCT_SOURCE" => 1
                        ],

                        // [
                        //     "PRODUCT_CODE" => "9",
                        //     "PRODUCT_ID" => "309",
                        //     "PRODUCT_NAME" => "7 GB - 7 days (Gifting)",
                        //     "PRODUCT_AMOUNT" => 2940,
                        //     "PRODUCT_SOURCE" => 1
                        // ],

                        [
                            "PRODUCT_CODE" => "10",
                            "PRODUCT_ID" => "402",
                            "PRODUCT_NAME" => "11GB 7days (Gifting)",
                            "PRODUCT_AMOUNT" => 3395,
                            "PRODUCT_SOURCE" => 1
                        ],

                        // [
                        //     "PRODUCT_CODE" => "10",
                        //     "PRODUCT_ID" => "351",
                        //     "PRODUCT_NAME" => "10GB + 10 minutes + 2GB YouTube - 30 days (Gifting)",
                        //     "PRODUCT_AMOUNT" => 4365,
                        //     "PRODUCT_SOURCE" => 1
                        // ],

                        [
                            "PRODUCT_CODE" => "11",
                            "PRODUCT_ID" => "349",
                            "PRODUCT_NAME" => "12.5GB - 30 days (Gifting)",
                            "PRODUCT_AMOUNT" => 5335,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "11",
                            "PRODUCT_ID" => "348",
                            "PRODUCT_NAME" => "16GB +10 Minutes - 30 days (Gifting)",
                            "PRODUCT_AMOUNT" => 6305,
                            "PRODUCT_SOURCE" => 1
                        ],

                        // [
                        //     "PRODUCT_CODE" => "11",
                        //     "PRODUCT_ID" => "445",
                        //     "PRODUCT_NAME" => "36GB - 30 days (Gifting)",
                        //     "PRODUCT_AMOUNT" => 10626,
                        //     "PRODUCT_SOURCE" => 3
                        // ],

                        [
                            "PRODUCT_CODE" => "10",
                            "PRODUCT_ID" => "306",
                            "PRODUCT_NAME" => "75 GB - 30 days (GIFTING)",
                            "PRODUCT_AMOUNT" => 17460,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "11",
                            "PRODUCT_ID" => "307",
                            "PRODUCT_NAME" => "200 GB - 60 days (GIFTING)",
                            "PRODUCT_AMOUNT" => 49000,
                            "PRODUCT_SOURCE" => 1
                        ],

                    ]
                ]
            ],
            "glo" => [
                [
                    "ID" => "02",
                    "PRODUCT" => [
                        [
                            "PRODUCT_CODE" => "1",
                            "PRODUCT_ID" => "225",
                            "PRODUCT_NAME" => "200 MB - 14 days (CORPORATE)",
                            "PRODUCT_AMOUNT" => 80,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "2",
                            "PRODUCT_ID" => "203",
                            "PRODUCT_NAME" => "500.0 MB - 30 days (CORPORATE)",
                            "PRODUCT_AMOUNT" => 200,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "3",
                            "PRODUCT_ID" => "194",
                            "PRODUCT_NAME" => "1.0 GB - 30 days (CORPORATE)",
                            "PRODUCT_AMOUNT" => 400,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "8",
                            "PRODUCT_ID" => "286",
                            "PRODUCT_NAME" => "750 MB - 1 day (SME)",
                            "PRODUCT_AMOUNT" => 187,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "4",
                            "PRODUCT_ID" => "195",
                            "PRODUCT_NAME" => "2.0 GB - 30 days (CORPORATE)",
                            "PRODUCT_AMOUNT" => 800,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "9",
                            "PRODUCT_ID" => "288",
                            "PRODUCT_NAME" => "1.5 GB - 2 days (SME)",
                            "PRODUCT_AMOUNT" => 280,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "5",
                            "PRODUCT_ID" => "196",
                            "PRODUCT_NAME" => "3.0 GB - 30 days (CORPORATE)",
                            "PRODUCT_AMOUNT" => 1200,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "10",
                            "PRODUCT_ID" => "289",
                            "PRODUCT_NAME" => "2.5 GB - 2 days (SME)",
                            "PRODUCT_AMOUNT" => 468,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "6",
                            "PRODUCT_ID" => "197",
                            "PRODUCT_NAME" => "5.0 GB - 30 days (CORPORATE)",
                            "PRODUCT_AMOUNT" => 2000,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "7",
                            "PRODUCT_ID" => "200",
                            "PRODUCT_NAME" => "10.0 GB - 30 days (CORPORATE)",
                            "PRODUCT_AMOUNT" => 4000,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "11",
                            "PRODUCT_ID" => "290",
                            "PRODUCT_NAME" => "10 GB - 7 days (SME)",
                            "PRODUCT_AMOUNT" => 1875,
                            "PRODUCT_SOURCE" => 1
                        ],

                    ]
                ]
            ],
            "9mobile" => [
                [
                    "ID" => "03",
                    "PRODUCT" => [

                        [
                            "PRODUCT_CODE" => "7",
                            "PRODUCT_ID" => "221",
                            "PRODUCT_NAME" => "500.0 MB - 30 days (CORPORATE)",
                            "PRODUCT_AMOUNT" => 150,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "1",
                            "PRODUCT_ID" => "183",
                            "PRODUCT_NAME" => "1.0 GB - 30 days (CORPORATE)",
                            "PRODUCT_AMOUNT" => 300,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "2",
                            "PRODUCT_ID" => "184",
                            "PRODUCT_NAME" => "1.5 GB - 30 days (CORPORATE)",
                            "PRODUCT_AMOUNT" => 450,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "3",
                            "PRODUCT_ID" => "185",
                            "PRODUCT_NAME" => "2 GB - 30 days (CORPORATE)",
                            "PRODUCT_AMOUNT" => 600,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "4",
                            "PRODUCT_ID" => "186",
                            "PRODUCT_NAME" => "3 GB - 30 days (CORPORATE)",
                            "PRODUCT_AMOUNT" => 900,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "4",
                            "PRODUCT_ID" => "265",
                            "PRODUCT_NAME" => "4 GB - 30 days (CORPORATE)",
                            "PRODUCT_AMOUNT" => 1200,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "6",
                            "PRODUCT_ID" => "188",
                            "PRODUCT_NAME" => "5 GB - 30 days (CORPORATE)",
                            "PRODUCT_AMOUNT" => 1500,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "5",
                            "PRODUCT_ID" => "189",
                            "PRODUCT_NAME" => "10 GB - 30 days (CORPORATE)",
                            "PRODUCT_AMOUNT" => 3000,
                            "PRODUCT_SOURCE" => 1
                        ],

                        [
                            "PRODUCT_CODE" => "7",
                            "PRODUCT_ID" => "229",
                            "PRODUCT_NAME" => "20.0 GB - Monthly (CORPORATE)",
                            "PRODUCT_AMOUNT" => 6000,
                            "PRODUCT_SOURCE" => 1
                        ]

                    ]
                ]
            ],
            "airtel" => [
                [
                    "ID" => "04",
                    "PRODUCT" => [

                        [
                            "PRODUCT_CODE" => "1",
                            "PRODUCT_ID" => "310",
                            "PRODUCT_NAME" => "150MB - 1 day (SME)",
                            "PRODUCT_AMOUNT" => 55,
                            "PRODUCT_SOURCE" => 1,
                        ],

                        // [
                        //     "PRODUCT_CODE" => "2",
                        //     "PRODUCT_ID" => "311",
                        //     "PRODUCT_NAME" => "300MB - 2 days (SME)",
                        //     "PRODUCT_AMOUNT" => 114,
                        //     "PRODUCT_SOURCE" => 1,
                        // ],

                        [
                            "PRODUCT_CODE" => "3",
                            "PRODUCT_ID" => "372",
                            "PRODUCT_NAME" => "500MB - 7 days (CORPORATE GIFTING)",
                            "PRODUCT_AMOUNT" => 514,
                            "PRODUCT_SOURCE" => 1,
                        ],
                        // [
                        //     "PRODUCT_CODE" => "4",
                        //     "PRODUCT_ID" => "360",
                        //     "PRODUCT_NAME" => "1GB - 2 days (SME)",
                        //     "PRODUCT_AMOUNT" => 340,
                        //     "PRODUCT_SOURCE" => 1,
                        // ],

                        [
                            "PRODUCT_CODE" => "5",
                            "PRODUCT_ID" => "375",
                            "PRODUCT_NAME" => "2GB - 30 days (CORPORATE GIFTING)",
                            "PRODUCT_AMOUNT" => 1514,
                            "PRODUCT_SOURCE" => 1,
                        ],

                        [
                            "PRODUCT_CODE" => "6",
                            "PRODUCT_ID" => "313",
                            "PRODUCT_NAME" => "3GB - 7 days (SME)",
                            "PRODUCT_AMOUNT" => 1014,
                            "PRODUCT_SOURCE" => 1,
                        ],

                        [
                            "PRODUCT_CODE" => "7",
                            "PRODUCT_ID" => "304",
                            "PRODUCT_NAME" => "7GB - 7 days (SME)",
                            "PRODUCT_AMOUNT" => 2014,
                            "PRODUCT_SOURCE" => 1,
                        ],

                        [
                            "PRODUCT_CODE" => "8",
                            "PRODUCT_ID" => "283",
                            "PRODUCT_NAME" => "10GB - Monthly (SME)",
                            "PRODUCT_AMOUNT" => 3014,
                            "PRODUCT_SOURCE" => 1,
                        ]
                    ]
                ]
            ]
        ];
        
        return $data;
    }


    // Data Plan for API
    public function DataPlansUser()
    {
        $data = $this->DataPlans();
        $percent = env("DATA_USER");

        // Process each product amount
        foreach ($data as &$network) {
            foreach ($network as &$networkDetails) {
                foreach ($networkDetails['PRODUCT'] as &$product) {
                    // Calculate the markup (6% of the product amount)
                    $markup = $product['PRODUCT_AMOUNT'] * $percent;

                    // Cap the markup to a minimum of 20 and a maximum of 100
                    $cappedMarkup = max(20, min(200, $markup));
                    $fixed = 50;

                    // Add the capped markup to the original product amount
                    // $product['PRODUCT_AMOUNT'];
                    // $product['PRODUCT_AMOUNT'] = $product['PRODUCT_AMOUNT'] + $cappedMarkup;
                    $product['PRODUCT_AMOUNT'] += $fixed;
                }
            }
        }
        return $data;
    }
    

}
