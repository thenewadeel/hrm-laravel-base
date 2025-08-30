#!/bin/bash

# Define the base directory
BASE_DIR="resources/views"

# Create the base directory if it doesn't exist
mkdir -p "$BASE_DIR"

# Create components directories
mkdir -p "$BASE_DIR/components/ui"
mkdir -p "$BASE_DIR/components/company"
mkdir -p "$BASE_DIR/components/unit"
mkdir -p "$BASE_DIR/components/employee"
mkdir -p "$BASE_DIR/components/user"

# Create livewire directories
mkdir -p "$BASE_DIR/livewire/company"
mkdir -p "$BASE_DIR/livewire/unit"
mkdir -p "$BASE_DIR/livewire/employee"
mkdir -p "$BASE_DIR/livewire/user"

# Create layouts directory
mkdir -p "$BASE_DIR/layouts"

echo "Directory structure created under $BASE_DIR"
