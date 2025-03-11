<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Config;
use Exception;

class ConfigController extends BaseController
{
    // Function to get config value by key
    public function getConfigValue($key)
    {
        try {
            $config = Config::where('config_key', $key)->first();
            if ($config) {
                return $this->sendSuccess('Config value retrieved successfully', ['config_value' => $config->config_value]);
            } else {
                return $this->sendError('Config key not found', [], 404);
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    // Function to update config value by key
    public function updateConfigValue(Request $request, $key)
    {
        try {
            $request->validate([
                'config_value' => 'required|string|max:255',
            ]);

            $config = Config::where('config_key', $key)->first();
            if ($config) {
                $config->config_value = $request->input('config_value');
                $config->save();
                return $this->sendSuccess('Config value updated successfully', $config);
            } else {
                return $this->sendError('Config key not found', [], 404);
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    // Function to delete config key/value
    public function deleteConfigKey($key)
    {
        try {
            $config = Config::where('config_key', $key)->first();
            if ($config) {
                $config->delete();
                return $this->sendSuccess('Config key deleted successfully');
            } else {
                return $this->sendError('Config key not found', [], 404);
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    // Function to add a new config key/value
    public function addConfigValue(Request $request)
    {
        try {
            $request->validate([
                'config_key' => 'required|string|max:255|unique:configs,config_key',
                'config_value' => 'required|string|max:255',
            ]);

            $config = new Config();
            $config->config_key = $request->input('config_key');
            $config->config_value = $request->input('config_value');
            $config->save();

            return $this->sendSuccess('Config value added successfully', $config);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
