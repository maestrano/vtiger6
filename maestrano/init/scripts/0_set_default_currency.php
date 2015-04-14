<?php

// Add default currency USD
$companyMapper = new CompanyMapper();
$currency = $companyMapper->findOrCreateCurrency('USD', -1);
$currency_id = $currency['id'];

// Fix existing User with broken currency to use default currency
global $adb;
$sql = "UPDATE vtiger_users u
          LEFT OUTER JOIN vtiger_currency_info ci ON (u.currency_id = ci.id)
        SET u.currency_id = ?
        WHERE ci.id IS NULL";
$adb->pquery($sql, array(CurrencyField::getDBCurrencyId()));
