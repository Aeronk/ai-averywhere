# Laravel GenAI

> Drop-in Generative AI for Laravel applications - opinionated, extensible, and production-ready.

[![Latest Version](https://img.shields.io/packagist/v/katema/laravel-genai.svg)](https://packagist.org/packages/katema/laravel-genai)
[![License](https://img.shields.io/packagist/l/katema/laravel-genai.svg)](LICENSE.md)

## Features

- 🎯 **Simple API** - Clean, Laravel-native syntax
- 🔌 **Provider Agnostic** - OpenAI, Claude, Gemini, Ollama, or custom
- 📝 **Prompt Management** - Version control your prompts
- 💾 **Context Management** - Maintain conversation context
- 🎨 **Model Integration** - Add AI to Eloquent models with traits
- 📊 **Cost Tracking** - Monitor tokens and costs
- 🔒 **Production Ready** - Rate limiting, validation, error handling
- 🚀 **Queue Support** - Async AI operations

## Installation

```bash
composer require katema/laravel-genai
php artisan genai:install
```

Set your API key in `.env`:

```env
GENAI_PROVIDER=openai
OPENAI_API_KEY=your-api-key-here
OPENAI_MODEL=gpt-4-turbo-preview
```

## Quick Start

### Basic Text Generation

```php
use Katema\LaravelGenAI\Facades\AI;

$response = AI::text('Write a product description for a coffee mug');
echo $response->content;
```

### Chat Conversations

```php
$messages = [
    ['role' => 'user', 'content' => 'What is Laravel?'],
];

$response = AI::chat($messages);
echo $response->content;
```

### Structured JSON Output

```php
$data = AI::json('Generate a user profile with name, email, and bio');
// Returns: ['name' => 'John Doe', 'email' => 'john@example.com', 'bio' => '...']
```

### With Context

```php
$response = AI::withContext([
    'user' => auth()->user()->name,
    'company' => 'Acme Corp'
])->text('Write a welcome email');
```

## Prompt Management

Create reusable prompts in `resources/prompts/`:

**prompts/marketing/product_description.md:**
```markdown
Generate a compelling product description for:

**Product:** {{product_name}}
**Category:** {{category}}
**Features:** {{features}}

Make it persuasive and highlight benefits.
```

Use it in your code:

```php
$response = AI::prompt('marketing.product_description', [
    'product_name' => 'Smart Coffee Maker',
    'category' => 'Kitchen Appliances',
    'features' => 'WiFi enabled, programmable, auto-shutoff'
]);
```

## Model Integration

Add AI capabilities to your Eloquent models:

```php
use Katema\LaravelGenAI\Traits\HasAI;

class Product extends Model
{
    use HasAI;
}
```

Then use it:

```php
$product = Product::find(1);

// Generate a summary
$summary = $product->summarize();

// Generate a description
$description = $product->describe();

// Ask questions about the model
$insights = $product->insights('What makes this product unique?');
```

## Multiple Providers

Switch between providers easily:

```php
// Use OpenAI
$response = AI::driver('openai')->text('Hello');

// Use Claude
$response = AI::driver('claude')->text('Hello');
```

Configure providers in `config/genai.php`:

```php
'providers' => [
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4-turbo-preview'),
    ],
    'claude' => [
        'api_key' => env('CLAUDE_API_KEY'),
        'model' => env('CLAUDE_MODEL', 'claude-3-5-sonnet-20241022'),
    ],
],
```

## Advanced Usage

### System Prompts

```php
$response = AI::withSystemPrompt('You are a helpful marketing assistant')
    ->chat($messages);
```

### Custom Options

```php
$response = AI::text('Write a story', [
    'temperature' => 0.9,
    'max_tokens' => 500,
    'model' => 'gpt-4'
]);
```

### Fresh Context

```php
AI::withContext(['user' => 'Alice'])
    ->text('First request');

// Clear context for next request
AI::fresh()->text('Second request');
```

## Cost Tracking

All responses include cost and token information:

```php
$response = AI::text('Hello');

echo $response->tokensUsed; // 150
echo $response->cost;       // 0.0045
echo $response->model;      // gpt-4-turbo-preview
echo $response->provider;   // openai
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=genai-config
```

Key configuration options:

```php
// Default provider
'default' => env('GENAI_PROVIDER', 'openai'),

// Rate limiting
'rate_limits' => [
    'enabled' => true,
    'max_requests_per_minute' => 60,
    'max_tokens_per_day' => 100000,
],

// Safety
'safety' => [
    'prompt_injection_detection' => true,
    'output_validation' => true,
    'max_prompt_length' => 10000,
],
```

## Artisan Commands

```bash
# Install the package
php artisan genai:install

# Create a new prompt template
php artisan genai:prompt marketing.email

# Test a prompt
php artisan genai:test marketing.email --var="product=Coffee"
```

## Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security issues, please email security@katema.dev instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Credits

- [Aaron Katema](https://github.com/Aeronk)
- [All Contributors](../../contributors)

---

Built with ❤️ for the Laravel community
