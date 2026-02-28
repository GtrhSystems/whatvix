#!/bin/bash

# Load variables from .env
if [ -f .env ]; then
  source .env
else
  echo ".env file not found!"
  exit 1
fi
# === CONFIGURATION ===
PORT="$PORT" #your node app port number loading from .env
APP_NAME="$NODE_APPNAME"  # your node app loading from .env
BASE_PATH="/www/server/nodejs/vhost"
PIDFILE="$BASE_PATH/pids/${APP_NAME}.pid"
START_SCRIPT="$BASE_PATH/scripts/${APP_NAME}.sh"


echo "[$(date)] Checking Node.js app ($APP_NAME)..."

# === STEP 1: Check if the app is running ===
if [ -f "$PIDFILE" ]; then
  APP_PID=$(cat "$PIDFILE")

  if kill -0 "$APP_PID" 2>/dev/null; then
    echo "[$(date)] App is running with PID $APP_PID. No action needed."
    exit 0
  else
    echo "[$(date)] App PID $APP_PID is not running."
  fi
else
  echo "[$(date)] PID file not found. Assuming app is not running."
fi

# === STEP 2: Kill any process holding the port (zombie or crashed) ===
PORT_PID=$(lsof -t -i:$PORT)
if [ "$PORT_PID" ]; then
  echo "[$(date)] Killing stuck process on port $PORT (PID: $PORT_PID)..."
  kill -9 "$PORT_PID"
else
  echo "[$(date)] No process found on port $PORT."
fi

# === STEP 3: Start the app ===
echo "[$(date)] Starting the app..."
bash "$START_SCRIPT"

# Optionally confirm it's running again:
sleep 2
NEW_PID=$(lsof -t -i:$PORT)
if [ "$NEW_PID" ]; then
  echo "[$(date)] App started successfully on port $PORT (PID: $NEW_PID)."
else
  echo "[$(date)] Failed to start the app. Please check logs."
fi
