#!/bin/bash

# Start servers for Dusk testing
echo "Starting Laravel development server..."
cd /home/gg/dev/hrm-laravel-base
php artisan serve --host=127.0.0.1 --port=8000 --no-reload > /tmp/laravel.log 2>&1 &
LARAVEL_PID=$!

echo "Starting ChromeDriver..."
vendor/laravel/dusk/bin/chromedriver-linux --port=9515 --silent --log-path=/tmp/chromedriver.log > /dev/null 2>&1 &
CHROME_PID=$!

echo "Waiting for servers to start..."
sleep 3

# Check if servers are running
if curl -s http://127.0.0.1:8000 > /dev/null; then
    echo "✓ Laravel server is running on port 8000"
else
    echo "✗ Laravel server failed to start"
    kill $LARAVEL_PID $CHROME_PID 2>/dev/null
    exit 1
fi

if curl -s http://127.0.0.1:9515/status > /dev/null; then
    echo "✓ ChromeDriver is running on port 9515"
else
    echo "✗ ChromeDriver failed to start"
    kill $LARAVEL_PID $CHROME_PID 2>/dev/null
    exit 1
fi

echo "Both servers are ready for Dusk testing!"
echo "Laravel PID: $LARAVEL_PID"
echo "ChromeDriver PID: $CHROME_PID"
echo ""
echo "To stop servers, run:"
echo "kill $LARAVEL_PID $CHROME_PID"