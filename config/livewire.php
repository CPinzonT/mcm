<?php

$config = require __DIR__.'/../vendor/livewire/livewire/config/livewire.php';

$config['temporary_file_upload']['rules'] = ['required', 'file', 'max:51200'];

return $config;
