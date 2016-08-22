<?php
if(!isset($comfig->refund)) $config->refund = new stdclass();
$config->refund->require = new stdclass();
$config->refund->require->create = 'name,money';
$config->refund->require->edit   = 'name,money';

$config->refund->list = new stdclass();
$config->refund->list->exportFields = 'id, createdBy, createdDate, dept, name, category, money, status, related, reviewer, refundBy, refundDate';
