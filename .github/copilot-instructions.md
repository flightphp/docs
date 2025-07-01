# Copilot AI Coding Assistant Instructions for FlightPHP Docs

Welcome to the FlightPHP Documentation Site! This project is the official documentation hub for the Flight PHP Framework. Please follow these guidelines when using AI coding assistants (like GitHub Copilot) to contribute to this repository.

## Project Structure

- **app/**: Contains the application logic for fetching, rendering, and displaying documentation content. This is a standard Flight PHP app, with customizations for documentation needs.
- **content/**: Holds all documentation content, organized by framework version (e.g., `v3/`).
  - **content/v3/en/**: The *only* directory for direct documentation edits. All other language folders are generated via translation scripts and should not be manually edited.
  - **content/v3/{lang}/**: Translated content for other languages. These are auto-generated and overwritten by scripts.
- **translate_content.php**: Script for translating English docs to other languages using an LLM API. Do not edit non-English docs directly.
- **public/**: Contains static assets and the main entry point (`index.php`).

## Contribution Guidelines

- **Add or update documentation only in `content/v3/en/`**. All other language folders are generated and will be overwritten.
- **When adding documentation, include practical, beginner-friendly examples**. Focus on helping new PHP developers understand the framework, but keep content useful for mid-level and senior devs too.
- **The tone should be casual, friendly, and fun**—like a good friend helping you learn. Avoid being overly silly or unprofessional.
- **This site is not a PHP tutorial**. Focus on Flight PHP concepts: framework basics, usage, scaling, best practices, and advanced features.
- **Do not edit translated files directly**. Use the translation script to update non-English docs.
- **Keep code and documentation clear and concise**. Favor real-world, copy-paste-ready examples.
- **If you add new features to the app logic, document them for future contributors.**
- **Check for existing examples before adding new ones** to avoid duplication.

## AI Assistant Usage

- When using AI assistants:
  - Ensure all changes to documentation are made in English under `content/v3/en/`.
  - For code changes, follow the existing structure and conventions in `app/`.
  - Suggest practical, beginner-friendly examples for new docs.
  - Maintain the project's friendly, approachable tone.
  - Do not generate or suggest edits to translated files.
  - If unsure about a translation or content structure, check the translation script or ask a maintainer.

## Other Notes

- The project uses standard Flight PHP conventions for routing and rendering.
- Static assets (CSS, JS, images) are in `public/`.
- The site is designed to be easy to run locally via php dev-server with `composer start`
- All contributions should be MIT licensed.

Thank you for helping make FlightPHP docs awesome!
