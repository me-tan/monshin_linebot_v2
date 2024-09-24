<?php
function getTargetFlexMessage($ary_from_gpt){
    $flexMessage = [
        "type" => "flex",
        "altText" => "this is a flex message",
        "contents" => [
          "type" => "carousel",
          "contents" => [
              [
                  "type" => "bubble",
                  "size" => "giga",
                  "direction" => "ltr",
                  "header" => [
                      "type" => "box",
                      "layout" => "vertical",
                      "contents" => [
                          [
                              "type" => "text",
                              "text" => "目標を選択してください",
                              "color" => "#ffffff",
                              "align" => "center",
                              "size" => "sm",
                              "gravity" => "center",
                              "decoration" => "none",
                              "position" => "relative",
                              "margin" => "none",
                              "wrap" => true,
                              "style" => "italic",
                              "weight" => "bold",
                          ],
                      ],
                      "backgroundColor" => "#27ACB2",
                      "paddingTop" => "19px",
                      "paddingAll" => "12px",
                      "paddingBottom" => "16px",
                  ],
                  "body" => [
                      "type" => "box",
                      "layout" => "vertical",
                      "contents" => [
                          [
                              "type" => "button",
                              "action" => [
                                  "type" => "message",
                                  "label" => $ary_from_gpt[0],
                                  "text" => "一番目を選択します！",
                              ],
                              "adjustMode" => "shrink-to-fit",
                              "style" => "secondary",
                          ],
                          [
                              "type" => "button",
                              "action" => [
                                  "type" => "message",
                                  "label" => $ary_from_gpt[1],
                                  "text" => "二番目を選択します！",
                              ],
                              "style" => "secondary",
                              "adjustMode" => "shrink-to-fit",
                          ],
                          [
                              "type" => "button",
                              "action" => [
                                  "type" => "message",
                                  "label" => $ary_from_gpt[2],
                                  "text" => "三番目を選択します！",
                              ],
                              "style" => "secondary",
                              "adjustMode" => "shrink-to-fit",
                          ],
                          [
                              "type" => "button",
                              "action" => [
                                  "type" => "message",
                                  "label" => $ary_from_gpt[3],
                                  "text" => "四番目を選択します！",
                              ],
                              "style" => "secondary",
                              "gravity" => "center",
                              "adjustMode" => "shrink-to-fit",
                          ],
                          [
                              "type" => "button",
                              "action" => [
                                  "type" => "message",
                                  "label" => "他の目標を見る",
                                  "text" => "他の目標を見たいです！",
                              ],
                              "style" => "secondary",
                              "gravity" => "center",
                              "adjustMode" => "shrink-to-fit",
                          ],
                      ],
                      "spacing" => "md",
                      "paddingAll" => "12px",
                  ],
              ],
          ],
        ]
    ]; // 適当にオウム返し
    return $flexMessage;
}

function getMnsnRegistration(){
    $flexMessage = [
        'type' => 'flex',
        'altText' => '問診票の登録完了',
        'contents' => [
            'type' => 'bubble',
            'body' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'button',
                        'action' => [
                            'type' => 'message',
                            'label' => '問診票の登録完了',
                            'text' => '問診票を記入しました！'
                        ]
                    ]
                ]
            ]
        ]
    ];
    return $flexMessage;
}

function confirmation() {
    $flexMessage = [
      "type" => "flex",
      "altText" => "this is a flex message",
      "contents" => [
        "type" => "carousel",
        "contents" => [
            [
                "type" => "bubble",
                "size" => "giga",
                "header" => [
                    "type" => "box",
                    "layout" => "vertical",
                    "contents" => [
                        [
                            "type" => "text",
                            "text" => "今日は目標達成できましたか？",
                            "align" => "center",
                            "size" => "lg",
                            "gravity" => "center",
                            "position" => "relative"
                        ]
                    ],
                    "paddingTop" => "19px",
                    "paddingAll" => "12px",
                    "paddingBottom" => "16px"
                ],
                "body" => [
                    "type" => "box",
                    "layout" => "vertical",
                    "contents" => [
                        [
                            "type" => "box",
                            "layout" => "horizontal",
                            "contents" => [
                                [
                                    "type" => "button",
                                    "action" => [
                                        "type" => "message",
                                        "label" => "はい",
                                        "text" => "できました！"
                                    ],
                                    "style" => "primary",
                                    "color" => "#f7082f",
                                    "position" => "relative",
                                    "margin" => "none",
                                    "height" => "md",
                                    "scaling" => true,
                                    "adjustMode" => "shrink-to-fit",
                                    "offsetStart" => "xs",
                                    "offsetEnd" => "none"
                                ],
                                [
                                    "type" => "button",
                                    "action" => [
                                        "type" => "message",
                                        "label" => "いいえ",
                                        "text" => "できませんでした"
                                    ],
                                    "style" => "primary",
                                    "color" => "#1b08f7"
                                ]
                            ],
                            "flex" => 1,
                            "spacing" => "md"
                        ]
                    ],
                    "spacing" => "lg",
                    "paddingAll" => "12px",
                    "borderWidth" => "light"
                ],
                "styles" => [
                    "footer" => [
                        "separator" => false
                    ]
                ]
            ]
        ]
      ]
    ];
    return $flexMessage;
  }

  function listManagement() {
    $flexMessage = [
      "type" => "flex",
      "altText" => "this is a flex message",
      "contents" => [
        "type" => "carousel",
        "contents" => [
            [
                "type" => "bubble",
                "body" => [
                    "type" => "box",
                    "layout" => "vertical",
                    "contents" => [
                        [
                            "type" => "button",
                            "action" => [
                                "type" => "message",
                                "label" => "運動を改善",
                                "text" => "運動を改善したい！"
                            ]
                        ]
                    ]
                ],
                "size" => "deca"
            ],
            [
                "type" => "bubble",
                "size" => "deca",
                "body" => [
                    "type" => "box",
                    "layout" => "vertical",
                    "contents" => [
                        [
                            "type" => "button",
                            "action" => [
                                "type" => "message",
                                "label" => "タバコを改善",
                                "text" => "タバコに関して改善したい！"
                            ]
                        ]
                    ]
                ]
            ],
            [
              "type" => "bubble",
              "body" => [
                  "type" => "box",
                  "layout" => "vertical",
                  "contents" => [
                      [
                          "type" => "button",
                          "action" => [
                              "type" => "message",
                              "label" => "お酒を改善",
                              "text" => "お酒に関して改善したい！"
                          ]
                      ]
                  ]
              ],
              "size" => "deca"
          ],
          [
            "type" => "bubble",
            "body" => [
                "type" => "box",
                "layout" => "vertical",
                "contents" => [
                    [
                        "type" => "button",
                        "action" => [
                            "type" => "message",
                            "label" => "間食を改善",
                            "text" => "間食を改善したい！"
                        ]
                    ]
                ]
            ],
            "size" => "deca"
          ],
          [
            "type" => "bubble",
            "body" => [
                "type" => "box",
                "layout" => "vertical",
                "contents" => [
                    [
                        "type" => "button",
                        "action" => [
                            "type" => "message",
                            "label" => "朝食を改善",
                            "text" => "朝食を改善したい！"
                        ]
                    ]
                ]
            ],
            "size" => "deca"
          ],
          [
            "type" => "bubble",
            "body" => [
                "type" => "box",
                "layout" => "vertical",
                "contents" => [
                    [
                        "type" => "button",
                        "action" => [
                            "type" => "message",
                            "label" => "睡眠を改善",
                            "text" => "睡眠を改善したい！"
                        ]
                    ]
                ]
            ],
            "size" => "deca"
          ],
          [
            "type" => "bubble",
            "body" => [
                "type" => "box",
                "layout" => "vertical",
                "contents" => [
                    [
                        "type" => "button",
                        "action" => [
                            "type" => "message",
                            "label" => "肥満を改善",
                            "text" => "肥満を改善したい！"
                        ]
                    ]
                ]
            ],
            "size" => "deca"
          ]
        ]
      ]
    ];
    return $flexMessage;
  }

  function lastWeek($userRank) {
    $userRanks = [];
    $userRanks = $userRank . "位";
    $flexMessage1 = [
      "type" => "flex",
      "altText" => "this is a flex message",
      "contents" => [
        "type" => "bubble",
        "body" => [
            "type" => "box",
            "layout" => "vertical",
            "contents" => [
              [
                "type" => "text",
                "text" => "おめでとうございます！ 最終順位は",
                "wrap" => true,
                "color" => "#000000",
                "size" => "md"
              ],
              [
                "type" => "text",
                "text" => $userRanks,
                "wrap" => true,
                "color" => "#FF0000",
                "size" => "3xl",
                "weight" => "bold",
                "align" => "center"
              ],
              [
                "type" => "text",
                "text" => "です！",
                "wrap" => false,
                "color" => "#000000",
                "size" => "md",
                "align" => "end"
              ],
            ]
        ]
      ]
    ];
  
    return $flexMessage1;
  }

  function makeUser($userDay){
    for($i = 0; $i < 9; $i++){
      $userPoints["ユーザ" . ($i + 1)] = $userDay;
    }
    foreach ($userPoints as $user => $points) {
      $rankings[] = [
        'user' => $user,
        'point' => $points,
      ];
    }
  
    $flexMessage = [
      "type" => "flex",
      "altText" => "this is a flex message",
      "contents" => [
        "type" => "bubble",
        "body" => [
            "type" => "box",
            "layout" => "vertical",
            "contents" => [
                [
                    "type" => "box",
                    "layout" => "baseline",
                    "contents" => [
                        [
                            "type" => "text",
                            "text" => "次週のメンバー",
                            "weight" => "bold",
                            "size" => "xl",
                            "margin" => "md",
                            "decoration" => "none",
                            "position" => "relative",
                            "align" => "center",
                            "color" => "#CC6600"
                        ],
                      ]
                ],
                [
                    "type" => "box",
                    "layout" => "vertical",
                    "margin" => "lg",
                    "spacing" => "sm",
                    "contents" => [
                        [
                            "type" => "separator",
                            "color" => "#CCCCCC"
                        ]
                      ]
                        ],
                      ],
                ]
        ]
    ];
  
    foreach ($rankings as $ranking) {
      $backgroundColor = ($ranking["user"] === "あなた") ? "#F3FFD8" : "#FFFFFF";
      $flexMessage["contents"]["body"]["contents"][1]["contents"][] = [
        
          "type" => "box",
          "layout" => "baseline",
          "backgroundColor" => $backgroundColor, // 背景色を動的に設定
          "contents" => [
              [
                  "type" => "icon",
                  "url" => "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gray_star_28.png",
                  "size" => "sm"
              ],
              [
                  "type" => "text",
                  "text" => $ranking["user"] . "　継続" . $userDay . "日",
                  "wrap" => true,
                  "size" => "lg",
                  "color" => "#111111"
              ]
          ]
      ];
      $flexMessage["contents"]["body"]["contents"][1]["contents"][] = [
        "type" => "separator",
        "color" => "#CCCCCC"
      ];
    }
    return $flexMessage;
  }


  function ranking($resultRank, $dayNum) {

    error_log(print_r("23" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
  
  
    
    $rankingColor = ["#DAA520", "#808080", "#8c4841", "#111111", "#111111", "#111111", "#111111", "#111111", "#111111", "#111111"];
    $rankingIcon = ["https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png", "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png", "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png", "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gray_star_28.png", "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gray_star_28.png", "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gray_star_28.png", "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gray_star_28.png", "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gray_star_28.png", "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gray_star_28.png", "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gray_star_28.png"];
    $rankingSize = ["lg", "lg", "lg", "md", "md", "md", "md", "md", "md", "md"];
    
    $rankings = [];
    foreach($resultRank as $ranking){
  
      $rankings[] = ["rank" => $ranking['rank'], "user" => $ranking['user'], "icon" => $rankingIcon[$ranking['rank'] - 1], "color" => $rankingColor[$ranking['rank'] - 1], "size" => $rankingSize[$ranking['rank'] - 1], "keepDays" => $ranking['point']];
    }
  
    // $rankings = [
    //   ["rank" => $rank, "user" => "ユーザ１", "icon" => $rankingIcon[$rank - 1], "color" => $rankingColor[$rank - 1], "size" => $rankingSize[$rank - 1], "keepDays" => $keep_days]
      
    //   // 他のランキングデータも同様に追加
    // ];
  
    $rankingName = "ランキング " . $dayNum . "日目";
  
    $flexMessage = [
      "type" => "flex",
      "altText" => "this is a flex message",
      "contents" => [
        "type" => "bubble",
        "body" => [
            "type" => "box",
            "layout" => "vertical",
            "contents" => [
                [
                    "type" => "box",
                    "layout" => "baseline",
                    "contents" => [
                        [
                            "type" => "icon",
                            "url" => "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png",
                            "size" => "xl"
                        ],
                        [
                            "type" => "text",
                            "text" => $rankingName,
                            "weight" => "bold",
                            "size" => "xl",
                            "margin" => "md",
                            "decoration" => "none",
                            "position" => "relative",
                            "align" => "center",
                            "color" => "#CC6600"
                        ],
                        [
                            "type" => "icon",
                            "url" => "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png",
                            "size" => "xl"
                        ]
                    ]
                ],
                [
                    "type" => "box",
                    "layout" => "vertical",
                    "margin" => "lg",
                    "spacing" => "sm",
                    "contents" => [
                        [
                            "type" => "separator",
                            "color" => "#CCCCCC"
                        ]
                      ]
                        ],
                      ],
                ]
              ]
    ];
  
    foreach ($rankings as $ranking) {
      $backgroundColor = ($ranking["user"] === "あなた") ? "#F3FFD8" : "#FFFFFF";
      $flexMessage["contents"]["body"]["contents"][1]["contents"][] = [
          "type" => "box",
          "layout" => "baseline",
          "backgroundColor" => $backgroundColor, // 背景色を動的に設定
          "contents" => [
              [
                  "type" => "icon",
                  "url" => $ranking["icon"],
                  "size" => "sm"
              ],
              [
                  "type" => "text",
                  "text" => $ranking["rank"] . "位: " . $ranking["user"] . "　継続" . $ranking["keepDays"] . "日",
                  "wrap" => true,
                  "size" => $ranking["size"],
                  "color" => $ranking["color"]
              ]
          ]
      ];
      $flexMessage["contents"]["body"]["contents"][1]["contents"][] = [
          "type" => "separator",
          "color" => "#CCCCCC"
      ];
    }
  
    error_log(print_r("24" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
  
  
    return $flexMessage;
  }
  
  function tool() {
    $flexMessage = [
      "type" => "flex",
      "altText" => "this is a flex message",
      "contents" => [
        "type" => "carousel",
        "contents" => [
            [
                "type" => "bubble",
                "body" => [
                    "type" => "box",
                    "layout" => "vertical",
                    "contents" => [
                        [
                            "type" => "button",
                            "action" => [
                                "type" => "message",
                                "label" => "目標の確認",
                                "text" => "目標を確認したい！"
                            ]
                        ]
                    ]
                ],
                "size" => "deca"
            ],
            [
                "type" => "bubble",
                "size" => "deca",
                "body" => [
                    "type" => "box",
                    "layout" => "vertical",
                    "contents" => [
                        [
                            "type" => "button",
                            "action" => [
                                "type" => "message",
                                "label" => "継続日数の確認",
                                "text" => "継続日数を確認したい！"
                            ]
                        ]
                    ]
                ]
            ],
        ]
      ]
    ];
    return $flexMessage;
  }
?>