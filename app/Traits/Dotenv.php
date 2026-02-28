<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;

trait Dotenv
{

    public function editEnv($key, $value, $isBool = false)
    {
        $env = file_get_contents(base_path('.env'));
        if ($isBool == true) {

            if (env($key) == true) {
                $boolKey = $key . "=true";
            } else {
                $boolKey = $key . "=false";
            }
            if ($value == true) {
                $bool = $key . "=true";
            } else {
                $bool = $key . "=false";
            }
            $newText = str_replace($boolKey, $bool, $env);
        } else {
            $newText = str_replace($key . '=' . env($key), $key . '=' . $this->removeEmptySpace($value), $env);
        }

        if (env($key) === null) {
            $newText = $newText . $key . '=' . $value . "\n";
        }
        File::put(base_path('.env'), $newText);
        return true;
    }

    public function updateEnv(array $keyValues)
    {
        foreach ($keyValues as $key => $value) {
            $this->editEnv($key, $value, is_bool($value));
        }
    }

    public function editWaServerEnv($key, $value, $isBool = false)
    {
        $envPath = base_path('/whatsapp-server/.env');

        if (!File::exists($envPath)) {
            throw new \Exception("The .env file does not exist at the specified path.");
        }

        $env = file_get_contents($envPath);
        if ($env === false) {
            throw new \Exception("Failed to read the .env file.");
        }

        $newValue = $isBool ? ($value ? "true" : "false") : trim($value);

        if (preg_match("/^{$key}=.*/m", $env)) {
            $newEnv = preg_replace("/^{$key}=.*/m", "{$key}={$this->removeEmptySpace($newValue)}", $env);
        } else {
            $newEnv = $env . "\n{$key}={$this->removeEmptySpace($newValue)}\n";
        }

        if (File::put($envPath, $newEnv) === false) {
            throw new \Exception("Failed to write to the .env file.");
        }

        return true;
    }


    public function removeEmptySpace($value = '')
    {
        return preg_replace('/\s+/', '', $value);
    }
}
