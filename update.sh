#!/bin/bash

# Prompt the user for a commit message
echo "Enter commit message: "
read commit_message

# Check if a message was provided
if [ -z "$commit_message" ]; then
  echo "Commit message cannot be empty."
  exit 1
fi

# Add changes to staging
git add .

# Commit with the provided message
git commit -m "$commit_message"

# Confirm success
echo "Committed with message: '$commit_message'"
