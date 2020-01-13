<?php

return [
    /**
     * 账户是否可以为负数
     */
    'can_minus'    => [
        'balance' => false,
        'score'   => false,
    ],

    /**
     * 是否立即扣款
     */
    'deductions'   => false,

    /**
     * 账户类型
     */
    'account_type' => [
        'balance' => '账户余额',
        'score'   => '积分账户',
    ],
];
