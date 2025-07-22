#!/bin/bash

# === COLORS ===
GREEN="\033[0;32m"
RED="\033[0;31m"
YELLOW="\033[1;33m"
RESET="\033[0m"

# === CHECK GIT INIT ===
if [ ! -d ".git" ]; then
  echo -e "${YELLOW}🛠️  No Git repo found. Initializing...${RESET}"
  git init
  read -p "📦 Enter remote GitHub URL (e.g., https://github.com/you/repo.git): " REMOTE_URL
  git remote add origin "$REMOTE_URL"
  git branch -M main
fi

# === MAIN MENU ===
echo ""
echo "🚀 What do you want to do?"
select action in "Commit" "Push" "Pull" "Commit & Push" "Exit"; do
  case $action in
    "Commit")
      read -p "📝 Enter commit message: " COMMIT_MSG
      if [ -z "$COMMIT_MSG" ]; then
        echo -e "${RED}❌ Commit message required.${RESET}"
        exit 1
      fi
      git add .
      git commit -m "$COMMIT_MSG"
      echo -e "${GREEN}✅ Changes committed.${RESET}"
      break
      ;;
    "Push")
      git push -u origin main
      echo -e "${GREEN}✅ Changes pushed to remote.${RESET}"
      break
      ;;
    "Pull")
      git pull origin main
      echo -e "${GREEN}📥 Latest changes pulled.${RESET}"
      break
      ;;
    "Commit & Push")
      read -p "📝 Enter commit message: " COMMIT_MSG
      if [ -z "$COMMIT_MSG" ]; then
        echo -e "${RED}❌ Commit message required.${RESET}"
        exit 1
      fi
      git add .
      git commit -m "$COMMIT_MSG"
      git push -u origin main
      echo -e "${GREEN}🚀 Changes committed and pushed.${RESET}"
      break
      ;;
    "Exit")
      echo "👋 Exiting..."
      exit 0
      ;;
    *)
      echo -e "${RED}❌ Invalid option. Choose a number from 1-5.${RESET}"
      ;;
  esac
done
