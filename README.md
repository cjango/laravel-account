# Laravel Account (Laravel 用户账户管理)

## 安装

```shell script
$ composer require jasonc/account

$ php artisan vendor:publish --tag="account"

$ php artisan migrate
```

## 使用

1. 在需要使用账户的模型中，引入Trait

use Jason\Account\Traits\HasAccount;

2. 执行账户规则

$account->rule(AccountRule $ruleId);

