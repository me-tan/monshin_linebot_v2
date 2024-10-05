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
                          [
                            "type" => "button",
                            "action" => [
                                "type" => "message",
                                "label" => "自分で目標を決める",
                                "text" => "自分で目標を決めたいです！",
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
                "size" => "micro",
                "body" => [
                    "type" => "box",
                    "layout" => "vertical",
                    "contents" => [
                        [
                            "type" => "button",
                            "action" => [
                                "type" => "message",
                                "label" => "ユーザ名の設定",
                                "text" => "ユーザ名を設定したい！"
                            ]
                        ]
                    ]
                ]
            ],
            [
                "type" => "bubble",
                "size" => "micro",
                "body" => [
                    "type" => "box",
                    "layout" => "vertical",
                    "contents" => [
                        [
                            "type" => "button",
                            "action" => [
                                "type" => "message",
                                "label" => "性別の設定",
                                "text" => "性別を設定したい！"
                            ]
                        ]
                    ]
                ]
            ],
            [
                "type" => "bubble",
                "size" => "micro",
                "body" => [
                    "type" => "box",
                    "layout" => "vertical",
                    "contents" => [
                        [
                            "type" => "button",
                            "action" => [
                                "type" => "message",
                                "label" => "性格の設定",
                                "text" => "性格を設定したい！"
                            ]
                        ]
                    ]
                ]
            ],
            [
                "type" => "bubble",
                "size" => "micro",
                "body" => [
                    "type" => "box",
                    "layout" => "vertical",
                    "contents" => [
                        [
                            "type" => "button",
                            "action" => [
                                "type" => "message",
                                "label" => "方言の設定",
                                "text" => "方言を設定したい！"
                            ]
                        ]
                    ]
                ]
            ],
            [
                "type" => "bubble",
                "size" => "micro",
                "body" => [
                    "type" => "box",
                    "layout" => "vertical",
                    "contents" => [
                        [
                            "type" => "button",
                            "action" => [
                                "type" => "message",
                                "label" => "通知時間の設定",
                                "text" => "通知時間を設定したい！"
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

  function personality(){
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
                                  "label" => "協調的な性格",
                                  "text" => "協調的な性格がいい！"
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
                                  "label" => "外向的な性格",
                                  "text" => "外向的な性格がいい！"
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
  function gender() {
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
                                  "label" => "男性",
                                  "text" => "男性がいい！"
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
                                  "label" => "女性",
                                  "text" => "女性がいい！"
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

  function dialect(){
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
                                  "label" => "標準語",
                                  "text" => "標準語がいい！"
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
                                "label" => "関西弁",
                                "text" => "関西弁がいい！"
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
  function group_data($groupName, $keep_days, $groupPoint){
    $userPoints = $keep_days . "pt" . "（継続" . $keep_days . "日）";
    $max_day = 6;
    $goal_points = 16;
    $max_group_point = 27;

    //ユーザポイントのメモリを作るための配列を作成
    if($keep_days < 7) {
        $array = array_merge(array_fill(0, $keep_days, '#FF4500'), array_fill(0, $max_day - $keep_days, '#FFE4E1'));
        $last_color = '#FFE4E1';
    } else {
        $array = array_fill(0, $max_day, '#FF4500');
        $last_color = '#FF4500';
    }

    //グループポイントのメモリを作るための配列を作成
    if($groupPoint < 17) {
        $first_array = array_merge(array_fill(0, $groupPoint, '#1E90FF'), array_fill(0, $goal_points - $groupPoint, '#ADD8E6'));
        $last_array = array_fill(0, $max_group_point - $goal_points, '#ADD8E6');
        $last_color2 = '#ADD8E6';
    } else if($groupPoint < 27) {
        $first_array = array_fill(0, $goal_points, '#1E90FF');
        $last_array = array_merge(array_fill(0, $groupPoint - $goal_points, '#1E90FF'), array_fill(0, $max_group_point - $groupPoint, '#ADD8E6'));
        $last_color2 = '#ADD8E6';
    }else {
        $first_array = array_fill(0, $goal_points, '#1E90FF');
        $last_array = array_fill(0, $max_group_point - $goal_points, '#1E90FF');
        $last_color2 = '#1E90FF';
    }


    $flexMessage = [
        "type" => "flex",
        "altText" => "this is a flex message",
        "contents" => [
            'type' => 'bubble',
            'body' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => 'チームメンバー',
                        'weight' => 'bold',
                        'size' => 'md',
                    ],
                    [
                        'type' => 'box',
                        'layout' => 'horizontal',
                        'contents' => [
                            [
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => [
                                    [
                                        'type' => 'text',
                                        'text' => $groupName[0],
                                        'align' => 'center',
                                        'size' => 'sm',
                                    ],
                                ],
                                'backgroundColor' => '#FAFAD2',
                                'width' => '50%',
                                'height' => '30px',
                                'justifyContent' => 'center',
                            ],
                            [
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => [
                                    [
                                        'type' => 'text',
                                        'text' => $groupName[1],
                                        'align' => 'center',
                                        'size' => 'sm',
                                    ],
                                ],
                                'backgroundColor' => '#FAFAD2',
                                'width' => '50%',
                                'height' => '30px',
                                'justifyContent' => 'center',
                            ],
                        ],
                        'spacing' => 'sm',
                        'margin' => 'xs',
                        'justifyContent' => 'center',
                    ],
                    [
                        'type' => 'box',
                        'layout' => 'horizontal',
                        'contents' => [
                            [
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => [
                                    [
                                        'type' => 'text',
                                        'text' => $groupName[2],
                                        'align' => 'center',
                                        'size' => 'sm',
                                    ],
                                ],
                                'backgroundColor' => '#FAFAD2',
                                'width' => '50%',
                                'height' => '30px',
                                'justifyContent' => 'center',
                            ],
                            [
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => [
                                    [
                                        'type' => 'text',
                                        'text' => $groupName[3],
                                        'align' => 'center',
                                        'size' => 'sm',
                                    ],
                                ],
                                'backgroundColor' => '#FAFAD2',
                                'width' => '50%',
                                'height' => '30px',
                                'justifyContent' => 'center',
                            ],
                        ],
                        'margin' => 'sm',
                        'justifyContent' => 'center',
                        'spacing' => 'sm',
                    ],
                    [
                        'type' => 'text',
                        'text' => 'あなたのポイント',
                        'margin' => 'sm',
                        'weight' => 'bold',
                    ],
                    [
                        'type' => 'box',
                        'layout' => 'horizontal',
                        'contents' => [
                            [
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => [
                                    [
                                        'type' => 'text',
                                        'text' => $userPoints,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'box',
                        'layout' => 'horizontal',
                        'contents' => array_merge(array_map(function ($color) {
                            return [
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => [],
                                'width' => '14%',
                                'backgroundColor' => $color,
                                'borderWidth' => 'light',
                                'borderColor' => ($color === '#FFE4E1') ? '#FFFFFF' : '#FFE4E1',
                            ];
                        }, $array),
                        [
                            [
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => [],
                                'width' => '20%',
                                'backgroundColor' => $last_color,
                                'borderWidth' => 'light',
                                'borderColor' => ($last_color === '#FFE4E1') ? '#FFFFFF' : '#FFE4E1',
                            ],

                        ]),
                        'height' => '20px',
                        'cornerRadius' => '10px',
                        'margin' => 'sm',
                        'width' => '25%',
                    ],
                    [
                        'type' => 'text',
                        'text' => 'チームの総ポイント',
                        'margin' => 'lg',
                        'weight' => 'bold',
                    ],
                    [
                        'type' => 'box',
                        'layout' => 'horizontal',
                        'contents' => [
                            [
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => [
                                    [
                                        'type' => 'text',
                                        'text' => $groupPoint . 'pt',
                                        'size' => 'md',
                                    ],
                                ],
                                'width' => '57%',
                            ],
                            [
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => [
                                    [
                                        'type' => 'text',
                                        'text' => 'goal',
                                        'size' => 'sm',
                                    ],
                                ],
                                'justifyContent' => 'flex-end',
                            ],
                        ],
                    ],
                    [
                        'type' => 'box',
                        'layout' => 'horizontal',
                        'contents' => array_merge(array_map(function ($color) {
                            return [
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => [],
                                'width' => '3.5%',
                                'backgroundColor' => $color,
                                'borderWidth' => 'light',
                                'borderColor' => ($color === '#ADD8E6') ? '#FFFFFF' : '#ADD8E6',
                            ];
                        }, $first_array), 
                        [
                            [
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => [],
                                'width' => '1%',
                                'backgroundColor' => '#ff8c00',
                            ],

                        ],
                        array_map(function ($color) {
                            return [
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => [],
                                'width' => '3.5%',
                                'backgroundColor' => $color,
                                'borderWidth' => 'light',
                                'borderColor' => ($color === '#ADD8E6') ? '#FFFFFF' : '#ADD8E6',
                            ];
                        }, $last_array),
                        [
                            [
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => [],
                                'width' => '5%',
                                'backgroundColor' => $last_color2,
                                'borderWidth' => 'light',
                                'borderColor' => ($last_color2 === '#ADD8E6') ? '#FFFFFF' : '#ADD8E6',
                            ],
                        ]),
                        'height' => '20px',
                        'cornerRadius' => '10px',
                        'margin' => 'sm',
                    ],
                ],
                'width' => '100%',
            ],
        ],
    ];
    return $flexMessage;
}
function check_set_data($userName, $notificationTime, $target, $chatPersonality, $chatGender, $chatDialect) {
    return [
        "type" => "flex",
        "altText" => "設定内容",
        "contents" => [
            "type" => "bubble",
            "size" => "mega",
            "body" => [
                "type" => "box",
                "layout" => "vertical",
                "contents" => [
                    [
                        "type" => "text",
                        "text" => "設定内容",
                        "weight" => "bold",
                        "size" => "lg"
                    ],
                    [
                        "type" => "box",
                        "layout" => "vertical",
                        "margin" => "lg",
                        "spacing" => "xs",
                        "contents" => [
                            [
                                "type" => "box",
                                "layout" => "baseline",
                                "spacing" => "sm",
                                "contents" => [
                                    [
                                        "type" => "text",
                                        "text" => "ユーザ名",
                                        "color" => "#aaaaaa",
                                        "size" => "md",
                                        "flex" => 5
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => $userName,
                                        "wrap" => true,
                                        "color" => "#666666",
                                        "size" => "md",
                                        "flex" => 5
                                    ]
                                ]
                            ],
                            [
                                "type" => "box",
                                "layout" => "baseline",
                                "spacing" => "sm",
                                "contents" => [
                                    [
                                        "type" => "text",
                                        "text" => "チャットの性別",
                                        "color" => "#aaaaaa",
                                        "size" => "md",
                                        "flex" => 5
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => $chatGender,
                                        "wrap" => true,
                                        "color" => "#666666",
                                        "size" => "md",
                                        "flex" => 5
                                    ]
                                ]
                            ],
                            [
                                "type" => "box",
                                "layout" => "baseline",
                                "spacing" => "sm",
                                "contents" => [
                                    [
                                        "type" => "text",
                                        "text" => "チャットの性格",
                                        "color" => "#aaaaaa",
                                        "size" => "md",
                                        "flex" => 5
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => $chatPersonality,
                                        "wrap" => true,
                                        "color" => "#666666",
                                        "size" => "md",
                                        "flex" => 5
                                    ]
                                ]
                            ],
                            [
                                "type" => "box",
                                "layout" => "baseline",
                                "spacing" => "sm",
                                "contents" => [
                                    [
                                        "type" => "text",
                                        "text" => "チャットの方言",
                                        "color" => "#aaaaaa",
                                        "size" => "md",
                                        "flex" => 5
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => $chatDialect,
                                        "wrap" => true,
                                        "color" => "#666666",
                                        "size" => "md",
                                        "flex" => 5
                                    ]
                                ]
                            ],
                            [
                                "type" => "box",
                                "layout" => "baseline",
                                "spacing" => "sm",
                                "contents" => [
                                    [
                                        "type" => "text",
                                        "text" => "目標",
                                        "color" => "#aaaaaa",
                                        "size" => "md",
                                        "flex" => 5
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => $target,
                                        "wrap" => true,
                                        "color" => "#666666",
                                        "size" => "md",
                                        "flex" => 5,
                                        "maxLines" => 0
                                    ]
                                ]
                            ],
                            [
                                "type" => "box",
                                "layout" => "baseline",
                                "spacing" => "sm",
                                "contents" => [
                                    [
                                        "type" => "text",
                                        "text" => "通知時間",
                                        "color" => "#aaaaaa",
                                        "size" => "md",
                                        "flex" => 5
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => $notificationTime,
                                        "wrap" => true,
                                        "color" => "#666666",
                                        "size" => "md",
                                        "flex" => 5
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];
}

function check_target_and_keep_days($dayNum, $keep_days, $target){
    return [
        "type" => "flex",
        "altText" => "現在の継続状況",
        "contents" => [
            "type" => "bubble",
            "size" => "mega",
            "body" => [
                "type" => "box",
                "layout" => "vertical",
                "contents" => [
                    [
                        "type" => "text",
                        "text" => "現在の継続状況",
                        "weight" => "bold",
                        "size" => "lg"
                    ],
                    [
                        "type" => "box",
                        "layout" => "vertical",
                        "margin" => "lg",
                        "spacing" => "xs",
                        "contents" => [
                            [
                                "type" => "box",
                                "layout" => "baseline",
                                "spacing" => "sm",
                                "contents" => [
                                    [
                                        "type" => "text",
                                        "text" => "目標",
                                        "color" => "#aaaaaa",
                                        "size" => "md",
                                        "flex" => 5
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => $target,
                                        "wrap" => true,
                                        "color" => "#666666",
                                        "size" => "md",
                                        "flex" => 5,
                                        "maxLines" => 0
                                    ]
                                ]
                            ],
                            [
                                "type" => "box",
                                "layout" => "baseline",
                                "spacing" => "sm",
                                "contents" => [
                                    [
                                        "type" => "text",
                                        "text" => "経過日数",
                                        "color" => "#aaaaaa",
                                        "size" => "md",
                                        "flex" => 5
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => $dayNum . "日",
                                        "wrap" => true,
                                        "color" => "#666666",
                                        "size" => "md",
                                        "flex" => 5
                                    ]
                                ]
                            ],
                            [
                                "type" => "box",
                                "layout" => "baseline",
                                "spacing" => "sm",
                                "contents" => [
                                    [
                                        "type" => "text",
                                        "text" => "継続日数",
                                        "color" => "#aaaaaa",
                                        "size" => "md",
                                        "flex" => 5
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => "継続" . $keep_days . "日",
                                        "wrap" => true,
                                        "color" => "#666666",
                                        "size" => "md",
                                        "flex" => 5
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];
}

?>