

```markdown
# 💼 Michael Abbey Dev Workspace

This is a unified Gitpod development workspace designed for managing and building multiple Laravel and AI-based projects efficiently.

---

## 📁 Workspace Structure

```

/workspace/michaelabbey-workspace/
├── agro360/            # Smart agriculture platform
├── stockpilot/         # Ghana Stock Exchange investing tool
├── fdm-portal/         # Marine engineering services portal
├── new project/        # Placeholder for fresh ideas
├── .local/bin/gitflow  # Git automation script
└── README.md

````

Each subfolder is an independent project, typically a Laravel application, AI service, or API-based system.

---

## 🔧 Tools & Features

- **Gitpod**: Cloud-based, always-ready development environment
- **Dockerized MySQL**: Persistent database storage with `mysql_data` volume
- **Gitflow Script (`gitflow`)**:
  - Auto-initializes Git repos
  - Handles commits, pushes, pulls with prompts
  - Detects the current project dynamically

---

## 🚀 Using `gitflow`

From any project folder:

```bash
gitflow
````

Options include:

* Commit changes with a custom message
* Push to remote repository
* Pull latest updates
* Initialize a Git repository and add a remote

---

## 🐳 Persistent MySQL Setup

Data is stored persistently using Docker:

```bash
docker-compose up -d
```

This uses a local `mysql_data` Docker volume, so even if the container is stopped or deleted, your data remains.

---

## 🧠 Tips

* New Laravel projects can be added by creating a new folder and running:

  ```bash
  laravel new project-name
  ```
* Initialize with `gitflow.sh` to set up Git remote, branch, and commit flow.
* Keep related AI scripts, knowledge models, and CLI tools in modular folders.

---

## 📜 License

All code in this workspace is the intellectual property of **Michael Abbey**. Licensed for private development use only unless explicitly stated otherwise.

```

