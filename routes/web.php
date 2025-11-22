<?php

$app->get('/', function ($request, $response) {
    return $response->write("CRM Slim 3 Rodando!");
});