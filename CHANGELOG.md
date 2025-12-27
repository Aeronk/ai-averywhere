# Changelog

All notable changes to Laravel GenAI will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release of Laravel GenAI
- Core AIManager with provider switching
- OpenAI provider implementation
- Claude provider implementation
- Prompt management system with file-based templates
- Variable interpolation in prompts
- HasAI trait for Eloquent models
- AI facade for easy access
- Cost and token tracking
- Context management
- System prompt support
- JSON structured output
- InstallCommand for easy setup
- PromptMakeCommand for creating prompt templates
- TestPromptCommand for testing prompts
- ai_requests migration for usage tracking
- Comprehensive configuration file
- Full documentation and usage examples

### Features
- 🎯 Simple, Laravel-native API
- 🔌 Provider agnostic architecture (OpenAI, Claude, Gemini, Ollama)
- 📝 File-based prompt management with version control
- 💾 Conversation context management
- 🎨 Eloquent model integration via traits
- 📊 Automatic cost and token tracking
- 🔒 Production-ready with rate limiting support
- 🚀 Queue support ready

## [0.1.0] - 2025-12-27

### Initial Development Release
- Package structure and foundation
- Core components (AIManager, contracts, DTOs)
- Provider implementations (OpenAI, Claude)
- Prompt management system
- Model integration
- Console commands
- Database migrations
- Documentation

---

**Note:** This package is in active development. APIs may change before v1.0.0 release.
