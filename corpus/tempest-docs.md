# Tempest Documentation Corpus

Real-world markdown harvested from the Tempest project website repository (tempestphp/tempest-docs, the same source benchmarked by the Tempest Markdown blog post). Blog articles and homepage code samples are concatenated below.

---

<!-- source: src/Web/Blog/articles/2024-10-02-alpha-2.md -->

---

title: Tempest alpha 2
description: Tempest alpha 2 is released with auth support, caching, and more!
author: brent
tag: Release

---

It's been three weeks since we released the first alpha version of Tempest, and since then, many people have joined and contributed to the project. It's been great seeing so many people excited about Tempest, on [Reddit](https://www.reddit.com/r/PHP/comments/1fi2dny/introducing_tempest_the_framework_that_gets_out/), [Twitter](https://x.com/LukeDowning19/status/1836083961174397420), [Discord](https://tempestphp.com/discord), and on [GitHub](https://github.com/tempestphp/tempest-framework).

Over the past three weeks, we made lots of bug fixes _and_ added lots of new features as well! In this blog post, I want to show the most prominent highlights: what's new in Tempest alpha 2!

By the way, this blog is new, we'll use it for Tempest-related updates. You can subscribe via [RSS](/rss) if you want to!

```
composer require tempest/framework:1.0-alpha2
```

## Authentication and Authorization

Being able to log in and protect routes is a pretty important feature of any framework. For alpha 2, we've laid the groundwork to build upon: Tempest handles user sessions, and checks their permissions with a clean API:

```php
$authenticator->login($user);
```

```php
final readonly class AdminController
{
    #[Get('/admin')]
    #[Allow(UserPermission::ADMIN)]
    public function admin(): Response
    { /* … */ }
}
```

What we haven't tackled yet, is user management — account registration, password resets, etc. We've deliberately left those features in the hand of framework users for now, since we're unsure how we want to handle these kinds of "higher level features".

The main question is: how opinionated should Tempest be? Should we provide all forms out of the box? How will we allow users to overwrite those? Which frontend stack(s) should we use? This is something we don't yet have an answer for, and would like to hear your feedback on as well.

## New website

You can't miss it: the Tempest website has gotten a great new design. Thanks to [Matt](https://github.com/tempestphp/tempest-docs/pull/20) who put a lot of effort into making something that's much nicer than what I could come up with! I like how the website visualizes Tempest's vision: to be modern and clean, sometimes a little bit slanted: we dare to go against what people take for granted, and we dare to rethink and venture into uncharted waters.

Thanks, Matt, for helping us visualize that vision!

## `str()` and `arr()` helpers

Next, we've added classes that wrap two of PHP's primitives: `StringHelper` and `ArrayHelper`. In practice though, you'd most likely use their `str()` and `arr()` shorthands.

Ideally, PHP would have built-in object primitives, but while we're waiting for that to ever happen, we wrote our own small wrappers around strings and arrays, and it turns out to be really useful.

Here are a couple of examples, but there is of course much more to it. I still need to write the docs, so for now I'll link to the [source](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Support/src/ArrayHelper.php)&nbsp;[code](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Support/src/StringHelper.php), it's no rocket science to understand what's going on!

Here are a couple of examples:

```php
if(str($path)
    ->trim('/')
    ->afterLast('/')
    ->matches('/\d+-/')
) {
    // …
}
```

```php
$arr
    ->map(fn (string $path) => /* … */ )
    ->filter(fn (string $content) => /* … */)
    ->map(fn (string $content) => /* … */ )
    ->mapTo(BlogPost::class);
```

By the way, we're always open for PRs that add more methods to these classes, so if you want to [contribute to Tempest](https://github.com/tempestphp/tempest-framework/blob/main/.github/CONTRIBUTING.md), this might be a good starting point!

## Cache

We also added a cache component, which is a small wrapper around [PSR-6](https://www.php-fig.org/psr/psr-6/). All PSR-6 compliant libraries can be plugged in, but we made the user-facing interface much simpler. I was inspired by an [awesome blogpost by Anthony Ferrera](https://blog.ircmaxell.com/2014/10/an-open-letter-to-php-fig.html), which talks about a cleaner approach to PSR-6 — a must-read!

Here's what caching in Tempest looks like in a nutshell:

```php
final readonly class RssController
{
    public function __construct(
        private Cache $cache
    ) {}

    public function __invoke(): Response
    {
        $rss = $this->cache->resolve(
            key: 'rss',
            cache: function () {
                return file_get_contents('https://stitcher.io/rss')
            },
            expiresAt: new DateTimeImmutable()->add(new DateInterval('P1D'))
        );
    }
}
```

You can read all the details about caching [in the docs](/main/features/cache).

## Discovery improvements

Finally, we made a lot of bugfixes and performance improvements to [discovery](/main/internals/discovery), one of Tempests most powerful features. Besides bugfixes, we've also started making discovery more powerful, for example by allowing vendor classes to be hidden from discovery:

```php
#[SkipDiscovery(except: [MigrationDiscovery::class])]
final class HiddenMigration implements Migration
{
    /* … */
}
```

On top of that, {gh:innocenzi} is working on a [`#[CanBePublished]` attribute](https://github.com/tempestphp/tempest-framework/pull/513), which is going to make third-party package development a lot easier. But that'll have to wait until alpha 3.

## Up next

Of course, there are a lot more small things fixed, changed, and added. You can read the full changelog here: [https://github.com/tempestphp/tempest-framework/releases/tag/1.0-alpha2](https://github.com/tempestphp/tempest-framework/releases/tag/1.0-alpha2).

So, what's next? We keep on working towards the next alpha version: {gh:aidan-casey,Aidan}'s working on a filesystem component, {gh:innocenzi} works on that `#[CanBePublished]` attribute, Sergiu is working on extended regex support for routing, and I'll tackle async command handling.

There's a lot going on, and we're super excited for it! Make sure to either [subscribe via RSS](https://tempestphp.com/rss) or [join our Discord](https://tempestphp.com/discord) if you want to stay up-to-date!

Until next time

<img class="w-[1.66em] shadow-md rounded-full" src="/tempest-logo.png" alt="Tempest" />

---

<!-- source: src/Web/Blog/articles/2024-10-31-alpha-3.md -->

---

title: Tempest alpha 3
description: Tempest alpha 3 is released with deferred tasks support, installers, a refactored view engine, and more!
author: brent
tag: Release

---

It's been a month since the previous alpha release of Tempest. Since then, we've merged [over 60 pull requests, created by 13 contributors](https://github.com/tempestphp/tempest-framework/pulls?q=is%3Apr+is%3Amerged+) and our [Discord server](https://tempestphp.com/discord) now has over 200 members.

I have to admit: I never imagined so many people would be interested in trying out and contributing to Tempest so early in the project's lifetime. A big _thank you_ to everyone who's contributing — either by trying out Tempest, making issues, or submitting PRs — you're awesome!

There's a lot of work to be done still, and today I'm happy to announce we've tagged the next alpha release. Let's take a look at what's new!

```
composer require tempest/framework:1.0-alpha.3
```

## Refactored Tempest View

One of the most significant refactors I've worked on since the dawn of Tempest: large parts of Tempest View have been rewritten. View files are now compiled and cached, and lots of bugs have been fixed.

```html
<x-base title="Home">
  <x-post :foreach="$this->posts as $post">
    {!! $post->title !!}

    <span :if="$this->showDate($post)"> {{ $post->date }} </span>
    <span :else> - </span>
  </x-post>
  <div :forelse>
    <p>It's quite empty here…</p>
  </div>

  <x-footer />
</x-base>
```

One of our most important TODOs now is **IDE support**. If you're reading this blog post and have experience with writing LSPs or IntelliJ language plugins, feel free to contact me via [email](mailto:brendt@stitcher.io) or [Discord](https://tempestphp.com/discord).

## `ArrayHelper` and `StringHelper` additions

During October, a handful of people have pitched in and added a lot of new functions to our [StringHelper](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Support/src/StringHelper.php) and [ArrayHelper](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Support/src/ArrayHelper.php) classes. The docs for these classes are still work in progress, but we've been using them all over the place, and they are really helpful.

```php
use function Tempest\Support\str;

$excerpt = str($content)
    ->excerpt(
        $previous->getLine() - 5,
        $previous->getLine() + 5,
        asArray: true,
    )
    ->map(function (string $line, int $number) use ($previous) {
        return sprintf(
            "%s%s | %s",
            $number === $previous->getLine() ? '> ' : '  ',
            $number,
            $line
        );
    })
    ->implode(PHP_EOL);
```

Special thanks to {gh:innocenzi}, {gh:yassiNebeL}, and {gh:gturpin-dev} for all the contributions!

## Custom route param regex

Tempest's router now supports regex parameters, giving you even more flexibility for route matching. Thanks to [Sergiu for the PR](https://github.com/tempestphp/tempest-framework/pull/486)!

```php
#[Get(uri: '/blog/{category}/{type:article|news}')]
public function category(string $category, string $type): Response
{
    // …
}
```

We're also still working on making the router [even more performant](https://github.com/tempestphp/tempest-framework/pull/626) (even though it already is pretty fast).

## Defer Helper

Inspired by Laravel, we added a `defer()` helper: any closure passed to it will be executed after the response has been sent to the client. This is especially useful for tasks that take a little bit more time and don't affect the response: analytics tracking, email sending, caching, …

```php
use function Tempest\defer;

final readonly class PageVisitedMiddleware implements HttpMiddleware
{
    public function __invoke(Request $request, callable $next): Response
    {
        defer(fn () => event(new PageVisited($request->getUri())));

        return $next($request);
    }
}
```

We still plan on adding asynchronous commands as well for even more complex background tasks, that's planned for the next alpha release.

## Initializers for built-in types

Vincent added support for [tagged built-in types](https://github.com/tempestphp/tempest-framework/pull/541) in the container. This feature can come in handy when you want to, for example, inject an array of grouped dependencies.

```php
final readonly class BookValidatorsInitializer implements Initializer
{
    #[Singleton(tag: 'book-validators')]
    public function initialize(Container $container): array
    {
        return [
            $container->get(HeaderValidator::class),
            $container->get(BodyValidator::class),
            $container->get(FooterValidator::class),
        ];
    }
}
```

```php
final readonly class BookService
{
    public function __construct(
        #[Tag('book-validators')] private array $validators,
    ) {}
}
```

## Closure-based event listeners

{gh:innocenzi} added support for [closure-based event listeners](https://github.com/tempestphp/tempest-framework/pull/540). These are useful to create local scoped event listeners that shouldn't be discovered globally.

```php
#[ConsoleCommand(name: 'migrate:down')]
public function __invoke(): void
{
	$this->eventBus->listen(MigrationFailed::class, function (MigrationFailed $event) {
		$this->console->error($event->exception->getMessage());
	});

	$this->migrationManager->up();
}
```

## ClassGenerator

{gh:innocenzi} also created [a wrapper for `nette/php-generator`](https://github.com/tempestphp/tempest-framework/pull/544), which opens the door for "make commands" and installers.

```php
use Tempest\Generation\ClassManipulator;

new ClassManipulator(PackageMigration::class)
    ->removeClassAttribute(SkipDiscovery::class)
    ->setNamespace('App\\Migrations')
    ->print();
```

## Installers

A pretty neat new feature in Tempest are installers: these are classes that know how to install a package or framework component. They are discovered automatically, and Tempest provides a CLI interface for them:

```console
./tempest install auth

<h2>Running the `auth` installer, continue?</h2> [<u><em>yes</em></u>/no]

<h2>app/User.php already exists. Do you want to overwrite it?</h2> [<u><em>yes</em></u>/no]
<success>app/User.php created</success>

<h2>app/UserMigration.php already exists. Do you want to overwrite it?</h2> [yes/<u><em>no</em></u>]

<h2>app/Permission.php already exists. Do you want to overwrite it?</h2> [yes/<u><em>no</em></u>]

<h2>app/PermissionMigration.php already exists. Do you want to overwrite it?</h2> [<u><em>yes</em></u>/no]
<success>app/PermissionMigration.php created</success>

<h2>app/UserPermission.php already exists Do you want to overwrite it?</h2> [yes/<u><em>no</em></u>]
<success>Done</success>
```

We're still fine-tuning the API, but here's what an installer looks like currently:

```php
use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;
use function Tempest\src_path;

final readonly class AuthInstaller implements Installer
{
    use PublishesFiles;

    public function getName(): string
    {
        return 'auth';
    }

    public function install(): void
    {
        $publishFiles = [
            __DIR__ . '/User.php' => src_path('User.php'),
            __DIR__ . '/UserMigration.php' => src_path('UserMigration.php'),
            __DIR__ . '/Permission.php' => src_path('Permission.php'),
            __DIR__ . '/PermissionMigration.php' => src_path('PermissionMigration.php'),
            __DIR__ . '/UserPermission.php' => src_path('UserPermission.php'),
            __DIR__ . '/UserPermissionMigration.php' => src_path('UserPermissionMigration.php'),
        ];

        foreach ($publishFiles as $source => $destination) {
            $this->publish(
                source: $source,
                destination: $destination,
            );
        }

        $this->publishImports();
    }
}
```

## Cache improvements

Finally, we've integrated the previously added cache component within several parts of the framework: discovery, config, and view compiling. We also added support for environment-based cache toggling.

```console
./tempest cache:status

<em>Tempest\Core\DiscoveryCache</em> <success>enabled</success>
<em>Tempest\Core\ConfigCache</em> <success>enabled</success>
<em>Tempest\Cache\ProjectCache</em> <error>disabled</error>
<em>Tempest\View\ViewCache</em> <error>disabled</error>
```

You can read more about caching [here](/main/features/cache).

## Up next

I am amazed by how much the community got done in a single month's time. Like I said at the start of this post: I didn't expect so many people to pitch in so early, and it's really encouraging to see.

That being said, there's still a lot of work to be done before a stable 1.0 release. We plan for the next alpha release to be available end of November, right after the PHP 8.4 release. These are the things we want to solve by then:

- Even more router improvements
- Async commands
- Filesystem
- Discovery cache improvements
- PHP 8.4 support — although this one will depend on whether our dependencies are able to update in time
- A handeful of [smaller improvements](https://github.com/tempestphp/tempest-framework/milestone/10)

If you want to help out with Tempest, the best starting point is to [join our Discord server](https://tempestphp.com/discord).

Until next time!

<img class="w-[1.66em] shadow-md rounded-full" src="/tempest-logo.png" alt="Tempest" />

---

<!-- source: src/Web/Blog/articles/2024-11-08-unfair-advantage.md -->

---

title: Unfair advantage
author: brent
description: Why Tempest instead of Symfony or Laravel?
tag: Thoughts

---

Someone asked me: [_why Tempest_](https://bsky.app/profile/laueist.bsky.social/post/3l7y5v3bm772y)? What areas do I expect Tempest to be better in than Laravel or Symfony? What gives me certainty that Laravel or Symfony won't just be able to copy what makes Tempest currently unique? What is Tempest's _unfair advantage_ compared to existing PHP frameworks?

I love this question: of course there is already a small group of people excited and vocal about Tempest, but does it really stand a chance against the real frameworks?

Ok so, here's my answer: Tempest's unfair advantage is **its ability to start from scratch and the courage to question and rethink the things we have gotten used to**.

Let me work through that with a couple of examples.

## The Curse

The curse of any mature project: with popularity comes the need for _backwards compatibility_. Laravel can't make 20 breaking changes over the course of one month; they can't add modern PHP features to the framework without making sure 10 years of code isn't affected too much. They have a huge userbase, and naturally prefer stability. If Tempest ever grows popular enough, we will have to deal with the same problem, we might make some different decisions when it comes to backwards compatibility, but for now it opens opportunities.

Combine that with the fact that Tempest started out in 2023 instead of 2011 as Laravel did or 2005 as Symfony did. PHP and its ecosystem have evolved tremendously. Laravel's facades are a good example: there is a small group of hard-core fans of facades to this day; but my view on facades (or better: service locators disguised behind magic methods) is that they represent a pattern that made sense at a time when PHP didn't have a proper type system (so no easy autowiring), where IDEs were a lot less popular (so no autocompletion and auto importing), and where static analysis in PHP was non-existent.

It makes sense that Laravel tried to find ways to make code as easy as possible to access within that context. Facades reduced a lot of friction during an era where PHP looked entirely different, and where we didn't have the language capabilities and tooling we have today.

That brings us back to the backwards compatibility curse: over the years, facades have become so ingrained into Laravel that it would be madness to try remove them today. It's naive to think the Tempest won't have its facade-like warts ten years from now — it will — but at this stage, we're lucky to be able to start from scratch where we can embrace modern PHP as the standard instead of the exception; and where tooling like IDEs, code formatters, and static analysers have become an integral part of PHP. To make that concrete:

- Tempest relies on attributes wherever possible, not as an option, but as the standard.
- We embraced enums from the start, and don't have to worry about supporting older variants.
- Tempest relies much more on reflection; its performance impact has become insignificant since the PHP 7 era.
- We can use the type system as much as possible: for dependency autowiring, console definitions, ORM and database models, event and command handlers, and more.

That _clean slate_ is an unfair advantage. Of course, it means nothing if you cannot convince enough people about the benefits of _your_ solution. That's where the second part comes in.

## The courage to question

The second part of Tempest's unfair advantage is the courage to question and rethink the things we have gotten used to. One of the best examples to illustrate this is `symfony/console`: the de-facto standard for console applications in PHP for over a decade. It's used everywhere, and it has the absolute monopoly when it comes to building console applications in PHP.

So I thought… what if I had to build a console framework today from scratch? What would that look like? Well, here's what a console command looks like in Symfony today:

```php
#[AsCommand(name: 'make:user')]
class MakeUserCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED)
            ->addOption('admin', null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $this->getArgument('email');
        $password = $this->getArgument('password');
        $isAdmin = $this->getOption('admin');

        // …

        return Command::SUCCESS;
    }
}
```

The same command in Laravel would look something like this:

```php
class MakeUser extends Command
{
    protected $signature = 'make:user {email} {password} {--admin}';

    public function handle(): void
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        $isAdmin = $this->option('admin');

        // …
    }
}
```

And here's Tempest's approach:

```php
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

final readonly class Make
{
    use HasConsole;

    #[ConsoleCommand]
    public function user(string $email, string $password, bool $isAdmin): void
    {
        // …
    }
}
```

Which differences do you notice?

- Compare the verbose `configure()` method in Symfony, vs Laravel's `$signature` string, vs Tempest's approach. Which one feels the most natural? The only thing you need to know in Tempest is PHP. In Symfony you need a separate configure method and learn about the configuration API, while in Laravel you need to remember the textual syntax for the signature property. That's all unnecessary boilerplate. Tempest skips all the boilerplate, and figures out how to build a console definition for you based on the PHP parameters you actually need. That's what's meant when we say that "Tempest gets out of your way". The framework helps you, not the other way around.

```console
~ ./tempest

<h2>Make</h2>
 <strong><em>make:user</strong></em> <<em>email</em>> <<em>password</em>> [<em>--admin</em>]
```

- Another difference is that Laravel's `Command` class extends from Symfony's implementation, which means its constructor isn't free for dependency injection. It's one of the things I dislike about Laravel: the convention that `handle()` methods can have injected dependencies. It's so confusing compared to other parts of the framework where dependencies are injected in the constructor. In Tempest, console commands don't extend from any class — in fact nothing does — there's a very good reason for this, inspired by Rust. If you want to learn more about that, you can watch me explain it [here](https://www.youtube.com/watch?v=HK9W5A-Doxc). The result is that any project class' constructor is free to use for dependency injection, which is the most obvious approach.
- Symfony's console commands must return an exit code — an integer. It's probably because of compatibility reasons that it's an int and not an enum. You can optionally return an exit code in Tempest as well, but of course it's an enum:

```php
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\ExitCode

final readonly class Package
{
    use HasConsole;

    #[ConsoleCommand]
    public function all(): ExitCode
    {
        if (! $this->hasBeenSetup()) {
            return ExitCode::ERROR;
        }

        // …

        return ExitCode::SUCCESS;
    }
}
```

- Something that's not obvious from these code samples is the fact that one of Tempest's more powerful features is [discovery](https://tempestphp.com/docs/internals/discovery/): Tempest will discover classes like controllers, console commands, view components, etc. for you, without you having to configure them anywhere. It's a really powerful feature that Symfony doesn't have, and Laravel only applies to a very limited extent.
- Finally, a feature that's not present in Symfony nor Laravel are console command middlewares. They work exactly as you expect them to work, just like HTTP middleware: they are executed in between the command invocation and handling. You can build you own middleware, or use some of Tempest's built-in middleware:

```php
use Tempest\Console\Middleware\CautionMiddleware;

final readonly class Make
{
    use HasConsole;

    #[ConsoleCommand(
        middleware: [CautionMiddleware::class]
    )]
    public function user(
        string $email,
        string $password,
        bool $isAdmin
    ): void {
        // …

        $this->success('Done!');
    }
}
```

```console
<h2>Caution! Do you wish to continue?</h2> [<em><u>yes</u></em>/no]

<comment>…</comment>

<success>Done!</success>
```

Now, you may like Tempest's style or not, I realize there's a subjective part to it as well. Practice shows though that more and more people do in fact like Tempest's approach, some even go out of their way to tell me about it:

> I must say I really enjoy what little I have seen from the Tempest until now and my next free-time project is going to be build with it. I have 20 years of experience at building webpages with PHP and Tempest is surprisingly close to how I envision web-development should look in 2024.
> — [/u/SparePartsHere](https://www.reddit.com/r/PHP/comments/1gg99la/tempest_alpha_3_releases_with_installer_support/luprt9i/)

> I really like the way this framework turns out. It is THE framework in the PHP space out there for which I am most excited about […]
> — [Wulfheart](https://github.com/tempestphp/tempest-framework/issues/681)

## Decisions

Two months ago, I released the first alpha version of Tempest, making very clear that I was still uncertain whether Tempest would actually become _a thing_ or not. And, sure, there are some important remarks to be made:

- Tempest is still in alpha, there are bugs and missing features, there is a lot of work to be done.
- It's impossible to rival the feature set of Laravel or Symfony, our initial target audience is a much smaller group of developers and projects. That might change in the future, but right now it's a reality we need to embrace.

But.

I've also seen a lot of involvement and interest in Tempest since its first alpha release. A small but dedicated community has begun to grow. We now almost have 250 members on [our Discord](https://tempestphp.com/discord), the [GitHub repository](https://github.com/tempestphp/tempest-framework) has almost reached 1k stars, we've merged 82 pull requests made by 12 people this past month, with 300 merged pull requests in total.

On top of that, we have a strong core team of experienced open-source developers: {gh:brendt,myself}, {gh:aidan-casey,Aidan}, and {gh:innocenzi,Enzo Innocenzi}, flanked by another [dozen contributors](https://github.com/tempestphp/tempest-framework/graphs/contributors).

We also decided to make Tempest's individual components available as standalone packages, so that people don't have to commit to Tempest in full, but can pull one or several of these components into their projects — Laravel, Symfony, or whatever they are building. {`tempest/console`} is probably the best example, but I'm very excited about {`tempest/view`} as well, and [there are more](https://tempestphp.com/docs/framework/standalone-components/).

All of that to say, my uncertainty about Tempest becoming _a thing_ or not, is quickly dissipating. People are excited about Tempest, more than I expected. It seems they are picking up on Tempest's unfair advantage, and I am excited for the future.

<img class="w-[1.66em] shadow-md rounded-full" src="/tempest-logo.png" alt="Tempest" />

---

<!-- source: src/Web/Blog/articles/2024-11-15-exit-codes-fallacy.md -->

---

title: Exit code fallacy
author: brent
description: Was I wrong about exit codes?
tag: Thoughts

---

Last week I wrote [a blog post](https://tempestphp.com/blog/unfair-advantage/) comparing Symfony,
Laravel, and Tempest. It was very well received and I got a lot of great feedback. One thing stood
out though:
a [handful](https://x.com/_Codito_/status/1855210473706197276) [of](https://phpc.social/@wouterj/113453310817058010) [people](https://www.reddit.com/r/PHP/comments/1gmgpa2/unfair_advantage/lw2fntc/)
were adamant that the way I designed exit codes for console commands was absolutely wrong.

I was surprised that one little detail grabbed so much attention, after all it was just one example
amongst others, but it prompted people to respond, which led me to think: was I wrong?

I want to share my thought process today. I think it's a fascinating exercise in software design, and it will help me further process the feedback I got. It might inspire you as well, so in my mind, a win-win!

## Setting the scene

I designed console commands to feel very similar to web requests: a client sends a
request, or invokes a command. There's an optional payload — the body in case of a request, input
arguments in case of a console command. The request or invocation is mapped to a handler — the
controller action or command handler; and that handler eventually returns a response or exit code.

I like that symmetry between controller actions and command handlers. It makes Tempest feel more
cohesive and consistent because there is familiarity between different parts of the framework.
If you know one part, you'll have a much easier time learning another part. I believe
familiarity is a great selling point if you want people to try out something new.

In case of console commands though, I had to figure out how to deal with return types. Any PHP script that's run via the console must eventually exit with an exit code: a number between 0 and 255, indicating some kind of status. If you don't manually provide one, PHP will do it for you.

Exit codes might feel very similar to HTTP response codes: you return a number that has a meaning. In most cases, the exit code will be `0`, meaning success. In case of an error, the exit code can be anything between `1` and `255`, but `1` is considered "a standard" everywhere: it simply means there was some kind of failure. But apart from that?

> Apart from zero and the macros EXIT_SUCCESS and EXIT_FAILURE, the C standard does not define the
> meaning of return codes. Rules for the use of return codes vary on different platforms (see the
> platform-specific sections). — [Wikipedia](https://en.wikipedia.org/wiki/Exit_status)

That's a pretty important distinction between HTTP response status codes and console exit codes: an application is allowed to assign whatever meaning they want to any exit code. Luckily, some exit codes are now so commonly used that everyone agrees on their meaning: `0` for success, `1` for generic error, but also `2` for invalid command usage, `25` for a cancelled command, or `127` when a command wasn't found, and a handful more.

Apart from those few, an exit could mean anything depending on the context it originated from. A pretty vague system if you'd ask me, but hey, it is what it is.

Ideally though, I wanted Tempest's exit codes to be represented by an enum, just like HTTP status codes. I like the discoverability of an enum: you don't have to figure out how to construct it, it's just a collection of values. By representing exit codes like `0`, `1`, and `2` in an enum, developers have a much easier time understanding the meaning of "standard" exit codes:

```php
enum ExitCode: int
{
    case SUCCESS = 0;
    case ERROR = 1;
    case INVALID = 2;

    // …
}
```

Obviously, I should add a handful more exit codes here.

I like how a developers don't have to worry about learning the right exit codes, they could simply use the `ExitCode` enum and find what's right for them. It's "self-documented" code, and I like it.

```php
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ExitCode

final readonly class Package
{
    #[ConsoleCommand]
    public function all(): ExitCode
    {
        if (! $this->hasBeenSetup()) {
            return ExitCode::ERROR;
        }

        // …

        return ExitCode::SUCCESS;
    }
}
```

Apart from an enum, I also allowed console commands to return `void`. Whenever nothing is returned, Tempest considers the command to have successfully finished, and thus return `0`. Whenever an error occurs or exception is thrown, Tempest will convert it to `1`.

```php
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ExitCode

final readonly class Package
{
    #[ConsoleCommand]
    public function all(): void
    {
        if (! $this->hasBeenSetup()) {
            throw new HasNotBeenSetup();
        }

        // Handle the command

        // Don't return anything
    }
}
```

When I talk about "focusing on the 95% case", this is a great example of what I
mean. 95% of console commands don't need fine-grained control over their exit codes. They take user
input, perform some actions, write output to the console, and will then exit successfully. Why
should developers be bothered with manually returning `0`, while it's only necessary to do so for edge cases? (I'm looking at you, Symfony 😅)

So, all in all, I like how the 95% case is solved:

- The `ExitCode` enum provides discoverability for commonly used exit codes.
- There's symmetry between HTTP status codes and console exit codes (both are enums in Tempest).
- Developers don't _have_ to return an exit code, Tempest will infer the most obvious one wherever possible.

But what about the real edge cases?

## My mistake

Whenever I say "focus on the 95% case", I also always add: "and make sure the other 5% is solvable, but it
doesn't have to be super convenient". And that's where I went wrong with my exit code design: I
wrapped the most common ones in an enum, but didn't account for all the other possibilities.

Ok, I actually did consider all other exit codes, but decided to ignore them "and revisit it later". This decision has led to a problem though, where the 5% use case cannot be solved! Developers simply can't return anything but those handful of predefined exit codes from a console command. That's a problem.

So, how to solve this? We brainstormed a couple of options on the [Tempest Discord](https://tempestphp.com/discord), and came up with two possible solutions:

#### 1. Exit codes as value objects

The downside of using an enum to model exit codes is that you can't have dynamic exit codes as they might differ in meaning depending on the context. An alternative to using an enum is to use a class instead — a value object:

```php
final readonly class ExitCode
{
    public function __construct(
        public int $code,
    ) {}

    public static function success(): self
    {
        return new self(0);
    }

    public static function error(): self
    {
        return new self(1);
    }
}
```

This way, you can still discover standard exit codes thanks to the static constructor, but you can also make custom ones wherever needed:

```php
class MyCommand
{
    #[ConsoleCommand]
    public function foo(): ExitCode
    {
        return ExitCode::success();
    }

    #[ConsoleCommand]
    public function bar(): ExitCode
    {
        return new ExitCode(48);
    }
}
```

On top of that, you could even throw an exception for invalid exit codes:

```php
final readonly class ExitCode
{
    public function __construct(
        public int $code,
    ) {
        if ($this->code < 0 || $this->code > 255) {
            throw new InvalidExitCode($this->code);
        }
    }

    // …
}
```

Not bad! Let's take a look at the other approach.

#### 2. Enums and ints

Let's say we keep our enum, but also allow console commands to return integers whenever people want to. In other words: the enum represents the exit codes that are "constant" or "standard", and all the other ones are represented by plain integers — if people really need them.

```php
class MyCommand
{
    #[ConsoleCommand]
    public function foo(): ExitCode
    {
        return ExitCode::SUCCESS;
    }

    #[ConsoleCommand]
    public function bar(): int
    {
        return 48;
    }
}
```

What are the benefits of this approach? To me, the biggest advantage here is the symmetry within the framework:

- There's already precedence of allowing multiple return types from command handlers and controller actions. Tempest knows how to deal with it. A controller action may return `Response` or `View`. A command handler may return `ExitCode` or `void`. Allowing `int` would be in line with that train of thought.
- HTTP response codes are modelled with an enum. Modelling exit codes with value objects would break symmetry. It would make the framework slightly less intuitive.
- Speaking of symmetry: Symfony and Laravel allow `int` as return types. Bash scripting requires an `int` to be returned. Allowing `int` is possibly something that people will instinctively reach for anyway. It would make sense.

Oh and, by the way: exit code validation could still be done with this approach, the only difference would be that the `InvalidExitCode` exception would be thrown from a different place, not when constructing the value object. The result for the end-user remains the same though: invalid exit codes will be blocked with an exception. Does it really matter to end users _where_ that exception originated from?

---

So those are the two options: value objects or enum + int. Of course, there are some possible variations like allowing both integers and value objects, using an interface and have the enum extend from it, or only allowing integers; but after lots of thinking, I settled on choosing between one of the two options I described.

And so the question is: now what? Well, I don't know, yet. I lean more towards the enum option because I value that symmetry most. But others disagree. I'd love to hear some more opinions though, so if you have something on your mind, feel free to share it [on the Tempest Discord](https://tempestphp.com/discord) (there's a discussion thread called "Console Command ExitCodes").

I hope to see you there, and be able to settle this question once and for all!

<img class="w-[1.66em] shadow-md rounded-full" src="/tempest-logo.png" alt="Tempest" />

---

<!-- source: src/Web/Blog/articles/2024-11-25-alpha-4.md -->

---

title: Tempest alpha 4
description: Tempest alpha 4 is released with support for asynchronous commands, the new filesystem component, partial discovery cache, and more!
author: brent
tag: Release

---

Once again a month has passed, and we're tagging a new alpha release of Tempest. This time we have over 70 merged pull requests by 12 contributors. We've also created a [backlog of issues](https://github.com/tempestphp/tempest-framework/milestone/12) to tackle before 1.0, it's a fast-shrinking list!

I'll share some more updates about the coming months at the end of this post, but first let's take a look at what's new and changed in Tempest alpha.4!

## Asynchronous Commands

Async commands are a new feature in Tempest that allow developers to handle tasks in a background process. Tempest already came with a [command bus](/main/essentials/console-commands) before this release, and running commands asynchronously is as easy as adding the `#[AsyncCommand]` attribute to a command class.

```php
// app/SendMail.php

use Tempest\CommandBus\AsyncCommand;

#[AsyncCommand]
final readonly class SendMail
{
    public function __construct(
        public string $to,
        public string $body,
    ) {}
}
```

Dispatching async commands is done exactly the same as dispatching normal commands:

```php
use function Tempest\command;

command(new SendMail(
    to: 'brendt@stitcher.io',
    body: 'Hello!'
));
```

Finally, in order to actually run the associated command handler after an async command has been dispatched, you'll have to run `./tempest command:monitor`. This console command should always be running, so you'll need to configure it as a daemon on your production server.

```console
~ ./tempest command:monitor
<success> Monitoring for new commands. Press ctrl+c to stop.</success>
```

While the core functionality of async command handling is in place, we plan on building more features like multi-driver support and balancing strategies on top of it in the future.

## Partial Discovery Cache

Before this release, discovery cache could either be on or off. This wasn't ideal for local development environments where you'd potentially have lots of vendor packages that have to be discovered as well. Partial discovery cache solves this by caching vendor code, but no project code.

Partial discovery cache is enabled via an environment variable:

```env
{:hl-comment:# .env:}
{:hl-property:DISCOVERY_CACHE:}={:hl-keyword:partial:}
```

This caching strategy comes with one additional requirement: it will only work whenever the partial cache has been generated. This is done via the `discovery:generate` command:

```console
~ ./tempest discovery:generate
<em>Clearing existing discovery cache…</em>
<success>Discovery cached has been cleared</success>
<em>Generating new discovery cache… (cache strategy used: partial)</em>
<success>Done</success> 111 items cached
```

The same manual generation is now also required when deploying to production with full discovery cache enabled. You can read more about automating this process in [the docs](/main/getting-started/installation#about-discovery). Finally, if you're interested in some more behind-the-scenes info and benchmarks, you can check out [the GitHub issue](https://github.com/tempestphp/tempest-framework/issues/395#issuecomment-2492127638).

## Make Commands

{gh:gturpin-dev} has laid the groundwork for a wide variaty of `make:` commands! The first ones are already added: `make:controller`, `make:model`, `make:request`, and `make:response`. There are many more to come!

```console
~ ./tempest make:controller FooController
<h2>Where do you want to save the file "FooController"?</h2> app/FooController.php
<success>Controller successfully created at "app/FooController.php".</success>
```

If you're interested in helping, you can [check out the list of TODO `make:` commands here](https://github.com/tempestphp/tempest-framework/issues/759). We're always welcoming to people who want to contribute!

## Filesystem Component

{gh:aidan-casey} added the first iteration of our filesystem component. The next step is to implement it all throughout the framework — there are many places where we're relying on PHP's suboptimal built-in file system API that could be replaced.

```php
use Tempest\Filesystem\LocalFilesystem;

$fs = new LocalFilesystem();

$fs->ensureDirectoryExists(root_path('.cache/discovery/partial/'));
```

## `#[Inject]` Attribute

The `#[Inject]` attribute can be used to tell the container that a property's value should be injected right after construction. This feature is especially useful with framework-provided traits, where you don't want to occupy the constructor within the trait.

```php
// Tempest/Console/src/HasConsole.php

use Tempest\Container\Inject;

trait HasConsole
{
    #[Inject]
    private Console $console;

    // …
}
```

You can read more about when and when not to use this feature [in the docs](/main/essentials/container#injected-properties).

## `config:show` Command

Samir added a new `config:show` command that dumps all loaded config in different formats.

```json
~ ./tempest config:show

{
    "…/vendor/tempest/framework/src/Tempest/Log/src/Config/logs.config.php": {
        "@type": "Tempest\\Log\\LogConfig",
        "channels": [],
        "prefix": "tempest",
        "debugLogPath": null,
        "serverLogPath": null
    },
    "…/vendor/tempest/framework/src/Tempest/Auth/src/Config/auth.config.php": {
        "@type": "Tempest\\Auth\\AuthConfig",
        "authenticatorClass": "Tempest\\Auth\\SessionAuthenticator",
        "userModelClass": "Tempest\\Auth\\Install\\User"
    },
    {:hl-comment:// …:}
}
```

This command can come in handy for debugging, as well as for future IDE integrations.

## Middleware Refactor

We made a small change to all middleware interfaces (HTTP, console, event bus, and command bus middlewares). The `$callable` argument of a middleware is now always properly typed, so that you get autocompletion in your IDE without having to add doc blocks.

As a comparison, this is what you had to write before:

```php
use Tempest\Router\HttpMiddleware;
use Tempest\Http\Request;
use Tempest\Http\Response;

class MyMiddleware implements HttpMiddleware
{
    public function __invoke(Request $request, callable $next): Response
    {
        /** @var \Tempest\Http\Response $response */
        $response = $next($request);

        // …
    }
}
```

And now you can write this:

```php
use Tempest\Router\HttpMiddleware;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Router\HttpMiddlewareCallable;

class MyMiddleware implements HttpMiddleware
{
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $response = $next($request);

        // …
    }
}
```

## Router Improvements

Next, Vincent made a lot of improvements to the router alongside contributions by many others. There's too much to show in detail, so I'll make another list with the highlights:

- [Router optimizations](https://github.com/tempestphp/tempest-framework/pull/626), [Router refactorings](https://github.com/tempestphp/tempest-framework/pull/666), and [regex optimizations](https://github.com/tempestphp/tempest-framework/pull/714) by {gh:blackshadev};
- [File upload mapping](https://github.com/tempestphp/tempest-framework/pull/702) by {gh:yassiNebeL};
- Support for [Delete](https://github.com/tempestphp/tempest-framework/pull/733), [Put, and Patch](https://github.com/tempestphp/tempest-framework/pull/742), by {gh:MrYamous}; and
- [Multiple routes per action](https://github.com/tempestphp/tempest-framework/pull/667), and [enum route binding](https://github.com/tempestphp/tempest-framework/pull/668) by {gh:brendt}.

## View Improvements

We added [boolean attribute support](https://github.com/tempestphp/tempest-framework/pull/700) in tempest/view:

```html
<option :value="$value" :selected="$selected">{{ $name }}</option>
```

## Database

Matthieu added support for [`json`](https://github.com/tempestphp/tempest-framework/pull/709) and [`set`](https://github.com/tempestphp/tempest-framework/pull/725) data types in the ORM:

```php
use Tempest\Database\Migration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

class BookMigration implements Migration
{
    public function up(): QueryStatement|null
    {
        return CreateTableStatement::forModel(Book::class))
            ->{:hl-property:set:}('setField', values: ['foo', 'bar'], default: 'foo')
            ->json('jsonField', default: '{"default": "foo"}');
    }

    // …
}
```

## Console Improvements

And finally, let's look at tempest/console: we added a range of small features to our console component:

- [negative arguments](https://github.com/tempestphp/tempest-framework/pull/660), [style injections](https://github.com/tempestphp/tempest-framework/pull/703), and [the "no prompt" mode](https://github.com/tempestphp/tempest-framework/pull/661) by {gh:innocenzi};
- [custom argument names](https://github.com/tempestphp/tempest-framework/pull/617) by {gh:gturpin-dev};
- [enum support](https://github.com/tempestphp/tempest-framework/pull/722) by {gh:aazsamir}; and
- improved [exit code](https://github.com/tempestphp/tempest-framework/pull/741) support by {gh:brendt}.

Besides all those smaller changes, {gh:innocenzi} is also working on a complete overhaul of the dynamic component system, it's still a work in progress, but it is looking great! You can [check out the full PR (with examples) here](https://github.com/tempestphp/tempest-framework/pull/754).

<video controls>
  <source src="/img/alpha-4-console-wip.mp4" type="video/mp4" />
</video>

---

And that's it! Well, actually, lots more things were done, but it's way too much to list in one blog post. These were the highlights, but you can also [read the full changelog](https://github.com/tempestphp/tempest-framework/releases/tag/v1.0.0-alpha.4) if you want to know all the details.

Once again, I'm amazed by how much the community is helping out with Tempest, at such an early stage of its lifecycle. I'm also looking forward to what's next: we plan to release alpha.5 somewhere mid-January. With it, we hope to support PHP 8.4 at the minimum, and update the whole framework to use new PHP 8.4 features wherever it makes sense. I blogged about the "why" behind that decision a while ago, if you're interested: [https://stitcher.io/blog/php-84-at-least](https://stitcher.io/blog/php-84-at-least).

PHP 8.4 is one of the last big things on our roadmap that's blocking a 1.0 release, so… 2025 will be a good year. If you want to be kept in the loop, [Discord](https://tempestphp.com/discord) is the place to be. If you're interested in contributing, then make sure to head over to the [alpha.5](https://github.com/tempestphp/tempest-framework/milestone/11) and [pre-1.0](https://github.com/tempestphp/tempest-framework/milestone/12) milestones. They give a pretty accurate overview of what's still on our plate before we tag the first stable release of Tempest. Exiting times!

Until next time!

<img class="w-[1.66em] shadow-md rounded-full" src="/tempest-logo.png" alt="Tempest" />

---

<!-- source: src/Web/Blog/articles/2025-01-16-start-with-the-customer-experience.md -->

---

title: Start with developer experience
description: Everything else is secondary.
author: brent
tag: Thoughts

---

Within the PhpStorm team, we're preparing a blog post that digests the results of our 2024 dev ecosystem survey, and I was asked to pitch in and comment on Laravel's success. I had more thoughts than what fit into that blog post, so I decided to write them down here.

Let's set the scene: data across platforms ([dev](https://www.jetbrains.com/lp/devecosystem-2023/php/#php_frameworks) [surveys](https://survey.stackoverflow.co/2024/technology/#1-web-frameworks-and-technologies), [packagist](https://packagist.org/packages/laravel/framework/stats), [GitHub](https://github.com/EvanLi/Github-Ranking/blob/master/Top100/PHP.md), …) shows that Laravel is by far the most popular framework in the PHP world today. It's interesting to see how, over the course of a decade, it went from being the underdog the most reputable PHP framework, even well known and looked at outside the PHP world.

This is the point where non-Laravel-PHP-developers might say they don't like Laravel — and they have all right to do so, I have a couple of grievances with Laravel as well. But data doesn't lie: around twice as many people are making a living with Laravel compared to Symfony. Note that that doesn't say anything about Symfony; it's a great framework! It _does_ mean that Laravel is far more poplar.

Why?

There are _a lot_ of factors in play when it comes to software's success, and it's naive to think that this blogpost will encapsulate all the details and intricacies. However, in my experience, there's one thing that stands out, one thing that has been the driving force behind Laravel's success. And how great is it that Steve Jobs already talked about it in 1997:

> You gotta start with the customer's experience, and work backwards towards the technology — [Steve Jobs, 1997](https://www.youtube.com/watch?v=XcG6CpxKFnU)

Start with the customer's experience. "Customers" being "developers" in the case of a framework. Laravel didn't care about best practices. It didn't care about what's "theoretically best". It didn't care about patterns and principles defined by a group of programmers two decades earlier.

It cared about what people had to write when they used Laravel. It put developer experience — DX — first.

I have to admit that there are many things about Laravel that I don't like. Things that I think are _wrong_. Things that _shouldn't be done that way_ — IMHO™. But at the end of the day? People get the job done with Laravel, and often with a lot less friction than other frameworks. Laravel is easier, faster, and — dare I say — more eloquent than other frameworks. The majority of developers and projects don't _need_ perfection, don't _need_ everything to be a 100% correct. They need frameworks that support _them_, and get out of their way.

Now, I could conclude this post by explaining how Tempest has that same mindset (which I'm cleverly doing by saying I won't do it 😉), _but I won't do that_. In all seriousness: I really wanted this post to be about giving kudos to Laravel. Since it's about framework development, I decided to write it on this blog instead of my personal one. I hope that works for everyone!

If anything, please [watch that full talk by Steve Jobs](https://www.youtube.com/watch?v=XcG6CpxKFnU), it's _really_ inspiring!

---

<!-- source: src/Web/Blog/articles/2025-01-22-alpha-5.md -->

---

title: Tempest alpha 5
description: Tempest alpha 5 is released with PHP 8.4 support, a major console overhaul, and more!
author: brent
tag: Release

---

It took a bit longer than anticipated, but Tempest alpha 5 is out. This release gets us an important step closer towards Tempest 1.0: support for PHP 8.4! Apart from that, {gh:innocenzi} has made a significant effort to improve our console component, and many, many other things have been added, fixed, and changed; this time by a total of 14 contributors.

Let's take a look!

```
composer require tempest/framework:1.0-alpha.5
```

## PHP 8.4

The main goal of this alpha release was to lay the groundwork for PHP 8.4 support. We've updated many of our interfaces to use property hooks instead of methods, which is a pretty big breaking change, but also feels very liberating. No more boring boilerplate getters!

```php
interface Request
{
    public Method $method { get; }

    public string $uri { get; }

    // …
}
```

Supporting PHP 8.4 as the minimum has been a goal for Tempest [from the start](https://stitcher.io/blog/php-84-at-least). While it's a bit annoying to deal with at the moment, I believe it'll be good for the framework in the long run.

Besides property hooks, we now also use PHP's new DOM parser for {`tempest/view`}, instead replying on third-party userland implementations. Most likely, we'll have to update a lot more 8.4-related tidbits, but the work up until this point has been very productive. Most importantly: all interfaces that should use property hooks now do, which I think is a huge win.

Something we noticed while upgrading to PHP 8.4: the biggest pain point for us isn't PHP itself, it's the **QA tools that don't support PHP 8.4 from the get-go**: Tempest relies on PHPStan, Rector, and PHP CS Fixer, and all these tools needed at least weeks after the PHP 8.4 release to have support for it. PHP CS Fixer, in fact, currently still doesn't support 8.4: running CS Fixer on an 8.4 codebase results in broken PHP files. PHP 8.4 specific feature support [will, most likely, have to wait a lot longer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/milestone/173).

**This is by no means a critique on those open source tools, rather it's a call for help from the PHP community**: so much of our code and projects (of the PHP community as a whole) relies on a handful of crucial QA tools, we should make sure there are enough resources (time and/or money) to make sure these tools can thrive.

## Console improvements

Apart from PHP 8.4, what I'm most excited about in this release are the features that {gh:innocenzi} worked on for weeks on end: he has made a tremendous effort to improve {`tempest/console`}, both from the UX side, the styling perspective, and architecturally.

```console
~ php tempest

<em>// TEMPEST</em>
This is an overview of available commands.
Type <u><command> --help</u> to get more help about a specific command.

          <em>// GENERAL</em>
             install   <dim>Applies the specified installer</dim>
              routes   <dim>Lists all registered routes</dim>
               serve   <dim>Starts a PHP development server</dim>
                tail   <dim>Tail multiple logs</dim>

            <em>// CACHE</em>
         cache:clear   <dim>Clears all or specified caches</dim>
        cache:status   <dim>Shows which caches are enabled</dim>

                       <comment>…</comment>
```

Besides many awesome UX changes — you should play around with them yourself to get a proper idea of what they are about — {gh:innocenzi} also reworked many of the internals. For example, you can now **pass enums into the ask component**:

```php
$this->console->ask(
    question: 'Pick a value',
    options: MyEnum::class,
    default: MyEnum::OTHER,
);
```

```console
<dim>│</dim> <em>Pick one or more</em>
<dim>│</dim> / <dim>Filter...</dim>
<dim>│</dim> → Foo
<dim>│</dim>   Bar
<dim>│</dim>   Baz
<dim>│</dim>   Other <dim>(default)</dim>
```

There's **a new key/value component**:

```php
$this->console->keyValue('Hello', 'World');
```

```console
Hello <dim>.......................................................</dim> World
```

And finally, **the task component**:

```php
$this->console->task('Working', fn () => sleep(1));
```

<video controls>
  <source src="/img/alpha-5-console-task.mp4" type="video/mp4" />
</video>

Of course, there's also a non-interactive version of the task component:

```console
~ php tempest test --no-interaction

Step 1 <dim>........................................</dim> 2025-02-22 06:07:36
Step 1 <dim>.......................................................</dim> <success>DONE</success>
Step 2 <dim>........................................</dim> 2025-02-22 06:07:37
Step 2 <dim>.......................................................</dim> <success>DONE</success>
```

I'm really excited to see how {`tempest/console`} is growing. For sure there are a lot of details to fine-tune, but it's going to be a great alternative to existing console frameworks. If you didn't know, by the way, {`tempest/console`} can be installed on its own in any project you want, not just Tempest projects.

## `tempest/view`

An important part of Tempest's vision is to think outside the box. One of the results of that outside-box-thinking is a new templating engine for PHP. I'm of course biased, but I really like how `{tempest/view`} leans much closer to HTML than other PHP templating engines. I would say that `{tempest/view`}'s goal is to make PHP templating more like HTML — the OG templating language — instead of the other way around.

Here's a short snippet of what `{tempest/view`} looks like:

```html
<x-base title="Home">
  <x-post :foreach="$this->posts as $post">
    {!! $post->title !!}

    <span :if="$this->showDate($post)"> {{ $post->date }} </span>
    <span :else> - </span>
  </x-post>
  <div :forelse>
    <p>It's quite empty here…</p>
  </div>

  <x-footer />
</x-base>
```

While this alpha release brings a bunch of small improvements and bugfixes, I'm most excited about something that's still upcoming: only recently, I've sat down with a colleague developer advocate at JetBrains, and we decided to work together on **IDE support for {`tempest/view`}**. This is huge, since a templating language is only as good as the support it has in your IDE: autocompletion, code insights, file references, … We're going to make all of that happen. It's a project that will take a couple of months, but I'm looking forward to see where it leads us!

## Vite support

Tempest now comes with optional Vite support. Simply run `php tempest install`, choose `vite`, and Tempest will take care of setting up your frontend stack for you:

<video controls>
  <source src="/img/alpha-5-vite.mp4" type="video/mp4" />
</video>

## A lot more!

I've shared the three main highlights of this release, but there have been a lot more features and fixes over the past two months, just to name a few:

- {gh:gturpin-dev} added a bunch of new `make:` commands
- `{txt}static:clean` now also clears empty directories
- Vincent has refactored and simplified route attributes
- I have done a bunch of small improvements in the database layer
- Discovery is now a standalone component, thanks to Alex
- And much [more](https://github.com/tempestphp/tempest-framework/releases/tag/v1.0.0-alpha.5)

Despite this release taking a bit longer than anticipated, I'm super happy and proud of what the Tempest community has achieved. Let's continue the work, I'm so looking forward to Tempest 1.0!

## On a personal note

I wanted to share some clarification why alpha 5 took longer to release. Mainly, it had to do with a number of real-life things: I went to some conferences, I got really sick with the flu, then my kids got really sick with the flu, and then I've been unfortunately dealing with severe heating problems in my house. There's lots of damage and costs, and insurance/the people involved still need to figure out who has to pay.

All of that lead to little time and energy to work on Tempest. I was really moved to see so many people still keeping up the work on Tempest, even though I had been rather unresponsive for a month or more. So here's hoping for a very productive Spring season! Thank you everyone who contributes!

<img class="w-[1.66em] shadow-md rounded-full" src="/tempest-logo.png" alt="Tempest" />

---

<!-- source: src/Web/Blog/articles/2025-02-02-chasing-bugs-down-rabbit-holes.md -->

---

title: Chasing bugs down rabbit holes
description: I had to debug the most interesting bug in Tempest to date.
author: brent
tag: Thoughts

---

It all started with me noticing the favicon of this website (the blog you're reading right now) was missing. My first thought was that the favicon file somehow got removed from the server, but a quick network inspection told me that wasn't the case: it showed no favicon request at all.

"Weird," I thought, I didn't remember making any changes to the layout code in ages. However, this website uses {`tempest/view`}, a new PHP templating engine, and I had been making lots of tweaks and fixes to it these past two weeks. It's still alpha, and naturally things break now and then. That's exactly the reason why I built this website with `tempest/view` from the very start: what better way to find bugs than to dogfood your own code?

So, next option: it's probably a bug in `tempest/view`. But where exactly? I inspected the source of the page — the compiled output of `tempest/view` — and discovered that the favicon was actually there:

```html
<link
  rel="icon"
  type="image/png"
  sizes="32x32"
  href="/favicon/favicon-32x32.png"
/>
```

So why wasn't it rendering? A closer inspection of the page source made it clear: _somehow_ the `{html}<link>` tag ended up in the `{html}<body>` of the HTML document:

```html
<html>
  <head>
    <title>Chasing Bugs down Rabbit Holes</title>

    <!-- … -->
  </head>
  <body>
    <!-- This shouldn't be here… -->
    <link
      rel="icon"
      type="image/png"
      sizes="32x32"
      href="/favicon/favicon-32x32.png"
    />
  </body>
</html>
```

Well, that's not good. Why does a tag that clearly belongs in `{html}<head>`, ends up in `{html}<body>`? I doubt I misplaced it. I opened the source and — as expected — it's in the correct place. I simplified the code a bit, but it's good enough to understand what's going on:

```html
<x-component name="x-base">
  <html lang="en">
    <head>
      <title :if="$title ?? null">{{ $title }} | Tempest</title>
      <title :else>Tempest</title>

      <link href="/main.css" rel="stylesheet" />

      <x-slot name="styles" />

      <!-- Clearly in head: -->
      <link
        rel="icon"
        type="image/png"
        sizes="32x32"
        href="/favicon/favicon-32x32.png"
      />

      <x-slot name="head" />
    </head>

    <body>
      <x-slot />

      <x-slot name="scripts" />
    </body>
  </html>
</x-component>
```

So what to do to debug a weird bug as this one? Create as small as possible a reproducible scenario in which the error occurs, and take it from there. So I commented out everything but the link tag and refreshed. Now it did end up in `{html}<head>`!

Weird.

So let's comment out a little less. Back and forth and back and forth; a little bit of commenting later and I discovered what set it off: whenever I removed that `{html}<x-slot name="styles"/>` tag before the `{html}<link>` tag, it worked. If I moved the `{html}<x-slot>` tag beneath the `{html}<link>` tag, it worked as well!

```html
<x-component name="x-base">
  <html lang="en">
    <head>
      <!-- … -->

      <!-- Removing this slot solves the issue: -->
      <!-- <x-slot name="styles"/> -->

      <link
        rel="icon"
        type="image/png"
        sizes="32x32"
        href="/favicon/favicon-32x32.png"
      />

      <!-- Moving it downstairs also solved it: -->
      <x-slot name="styles" />
    </head>
  </html>
</x-component>
```

This is the worst case scenario: apparently there's something wrong with slot rendering in `tempest/view`! Now, if you don't know, slots are a way to inject content into parent templates from within a child template. The `styles` slot, for example, can be used by any template that relies on the `{html}<x-base>` layout to inject styles into the right place:

```html
<!-- home.view.php -->

<x-base>
  Just some normal content ending up in body

  <x-slot name="styles">
    <!-- Additional styles injected into the parent's slot: -->

    <style>
      body {
        background: red;
      }
    </style>
  </x-slot>
</x-base>
```

Slots are one of the most complex parts of `tempest/view`, so naturally I dreaded heading back into that code. Especially since I wrote it about two months ago — an eternity it felt, no way I remembered how it worked. Luckily, I have gotten pretty good at source diving over the years, so after half an hour, I was up to speed again with my own code.

Important to know is that `tempest/view` relies on PHP's DOM parser to render templates. In contrast to most other PHP template engines who parse their templates with regex, `tempest/view` will parse everything into a DOM, and perform operations on that DOM. This approach gives a lot more flexibility, for example when it comes to attribute expressions like `{html}<div :foreach="$books as $book">`, but parsing a DOM is also more complex than regex find/replace operations.

My assumption was that either something went wrong in the DOM parser, or that `tempest/view` converting the DOM back into an HTML file messed something up. Since DOM parsing is done by PHP 8.4's built-in parser, I assumed I was at fault instead of PHP. However, no matter how far I searched, I could not find any place that would result in a tag being moved from `{html}<head>` to `{html}<body>`! In a final attempt, I decided to debug the DOM, regardless of my assumption that it couldn't be wrong. I took a compiled template from Tempest, passed it to PHP's built-in DOM parser, and observed what happened.

I made this component in Tempest:

```html
<x-component name="x-base">
  <html lang="en">
    <head>
      <x-slot name="styles" />
      <link
        rel="icon"
        type="image/png"
        sizes="32x32"
        href="/favicon/favicon-32x32.png"
      />
    </head>
  </html>
</x-component>
```

I then used that component in a template and dumped the compiled output:

```php
$compiled = $this->compiler->compile(<<<HTML
<x-base>
    <slot name="styles">Styles</slot>
</x-base>
HTML);

ld($compiled);
```

Finally, I manually passed that compiled output to PHP's DOM parser:

```php
$compiled = <<<HTML
<html lang="en">
<head>
    Styles
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png"/>
</head>
</html>
HTML;

$dom = HTMLDocument::createFromString($compiled, LIBXML_NOERROR | HTML_NO_DEFAULT_NS)
```

Now I made a mistake here which in the end turned out very lucky, because otherwise I would probably have spent a lot more time debugging: I injected the text `Styles` into the styles slot, instead of a valid style tag. This was just me being lazy, but it turned out to be the key to solving this problem.

I noticed that `Styles` caused the parsing to break somehow, because the parsed DOM looked like this:

```html
<html lang="en">
  <head> </head>
  <body>
    Styles
    <link
      rel="icon"
      type="image/png"
      sizes="32x32"
      href="/favicon/favicon-32x32.png"
    />
  </body>
</html>
```

This is when I realized: the DOM parser _probably_ only allows HTML tags in the `{html}<head>`, instead of any text! So I changed my `Styles` to `{html}<style></style>`, and suddenly it worked!

```html
<html lang="en">
  <head>
    <style></style>
  </head>
  <body>
    <link
      rel="icon"
      type="image/png"
      sizes="32x32"
      href="/favicon/favicon-32x32.png"
    />
  </body>
</html>
```

Ok, that makes sense: the parser kind of breaks when it encounters invalid text in `{html}<head>` (or so I thought); fair enough. In case of this website, there are probably some invalid styles injected into that slot, causing it to break.

"But hang on," I thought, "the page where it breaks doesn't have injected styles!" This is where the final piece of the puzzle came to be: the DOM parser doesn't just prevent text from being in `{html}<head>`, it prevents _any_ tag that doesn't belong in `{html}<head>` to be there!

_Whenever a slot is empty, `tempest/view` will keep the slot element untouched. It's a custom HTML element without any styling, it's basically nothing and doesn't matter_ — was my thinking two months ago.

Except when it ends up in the `{html}<head>` tag of an HTML document! See, this is invalid HTML:

```html
<html lang="en">
  <head>
    <x-slot name="styles" />
    <link
      rel="icon"
      type="image/png"
      sizes="32x32"
      href="/favicon/favicon-32x32.png"
    />
  </head>
  <body></body>
</html>
```

That's because `{html}<x-slot>` isn't a tag allowed within `{html}<head>`! And what does the DOM parser do when it encounters an element that doesn't belong in `{html}<head>`? It will simply close the `{html}<head>` and start the `{html}<body>`. Apparently that's part of [the spec](https://www.w3.org/TR/2011/WD-html5-20110113/tokenization.html#parsing-main-inhead) (thanks to {bsky:innocenzi.dev} for pointing that out)!

Why is it part of the spec? As far as I understand, HTML5 allows you to write something like this (note that there's no closing `{html}</head>` tag):

```html
<hmtl>
    <head>
        <title>Chasing Bugs down Rabbit Holes</title>
    <body>
        <h1>This is the body</h1>
    </body>
</hmtl>
```

Because `{html}<head>` only allows a specific set of tags that can't exist in `{html}<body>`, the DOM parser can infer when the `{html}<head>` is done, even if it doesn't have a closing tag. That's why custom elements like `{html}<x-slot name="styles" />` can't live in `{html}<head>`: as soon as the DOM parser encounters it, it'll assume it has entered the body, despite there being an explicit `{html}</head>` further down below.

This is one of these things where I think "this behaviour is bound to cause more problems than it solves." But it is part of the spec, and people much smarter than me have thought this through, so… ¯\\\_(ツ)\_/¯

In the end… the fix was simple: don't render slots when they don't have any content. Or comment them out so that they are still visible in the source code. That's what I settled on eventually:

```php
if ($slot === null) {
    // A slot doesn't have any content, so we'll comment it out.
    // This is to prevent DOM parsing errors (slots in <head> tags is one example, see #937)
    return '<!--' . $matches[0] . '-->';
}
```

A pretty simple fix after a pretty intense debugging session. Had I known the HTML5 spec by heart, I would probably have caught this earlier. But hey, we live and learn, and the feeling when I finally fixed it was pretty nice as well!

Until next time!

---

<!-- source: src/Web/Blog/articles/2025-03-08-static-websites-with-tempest.md -->

---

title: Static websites with Tempest
description: Tempest makes it super convenient to convert any controller action in statically generated pages.
author: brent
tag: Tutorial

---

Let's say you have a controller that shows blog posts — kind of like the page you're reading now:

```php
final readonly class BlogController
{
    #[Get('/blog')]
    public function index(BlogRepository $repository): View
    {
        $posts = $repository->all();

        return view(__DIR__ . '/blog_index.view.php', posts: $posts);
    }

    #[Get('/blog/{slug}')]
    public function show(string $slug, BlogRepository $repository): Response|View
    {
        $post = $repository->find($slug);

        return view(__DIR__ . '/blog_show.view.php', post: $post);
    }
}
```

These type of web pages are abundant: they show content that doesn't change based on the user viewing it — static content. Come to think of it, it's kind of inefficient having to boot a whole PHP framework to render exactly the same HTML over and over again with every request.

However, instead of messing around with complex caches in front of dynamic websites, what if you could mark a controller action as a "static page", and be done? That's exactly what Tempest allows you to do:

```php
use Tempest\Router\StaticPage;

final readonly class BlogController
{
    #[StaticPage]
    #[Get('/blog')]
    public function index(BlogRepository $repository): View
    {
        $posts = $repository->all();

        return view(__DIR__ . '/blog_index.view.php', posts: $posts);
    }

    // …
}
```

And… that's it! Now you only need to run `{console}tempest static:generate`, and Tempest will convert all controller actions marked with `#[StaticPage]` to static HTML pages:

```console
~ tempest static:generate

- <u>/blog</u> > <u>/web/tempestphp.com/public/blog/index.html</u>

<success>Done</success>
```

Hold on though… that's all fine for a page like `/blog`, but what about `/blog/{slug}` where you have multiple variants of the same static page based on the blog post's slug?

Well for static pages that rely on data, you'll have to take one more step: use a data provider to let Tempest know what variants of that page are available:

```php
use Tempest\Router\StaticPage;

final readonly class BlogController
{
    // …

    #[StaticPage(BlogDataProvider::class)]
    #[Get('/blog/{slug}')]
    public function show(string $slug, BlogRepository $repository): Response|View
    {
        // …
    }
}
```

The task of such a data provider is to supply Tempest with an array of strings for every variable required on this page. Here's what it looks like:

```php
use Tempest\Router\DataProvider;

final readonly class BlogDataProvider implements DataProvider
{
    public function __construct(
        private BlogRepository $repository,
    ) {}

    public function provide(): Generator
    {
        foreach ($this->repository->all() as $post) {
            yield ['slug' => $post->slug];
        }
    }
}
```

With that in place, let's rerun `{console}tempest static:generate`:

```console
~ tempest static:generate

- <u>/blog</u> > <u>/web/tempestphp.com/public/blog/index.html</u>
- <u>/blog/exit-codes-fallacy</u> > <u>/web/tempestphp.com/public/blog/exit-codes-fallacy/index.html</u>
- <u>/blog/unfair-advantage</u> > <u>/web/tempestphp.com/public/blog/unfair-advantage/index.html</u>
- <u>/blog/alpha-2</u> > <u>/web/tempestphp.com/public/blog/alpha-2/index.html</u>
<comment>…</comment>
- <u>/blog/alpha-5</u> > <u>/web/tempestphp.com/public/blog/alpha-5/index.html</u>
- <u>/blog/static-websites-with-tempest</u> > <u>/web/tempestphp.com/public/blog/static-websites-with-tempest/index.html</u>

<success>Done</success>
```

And we're done! All static pages are now available as static HTML pages that will be served by your webserver directly instead of having to boot Tempest. Also note that tempest generates `index.html` files within directories, so most webservers can serve these static pages directly without any additional server configuration required.

On a final note, you can always clean up the generated HTML files by running `{console}tempest static:clean`:

```console
~ tempest static:clean

- <u>/web/tempestphp.com/public/blog</u> directory removed
- <u>/web/tempestphp.com/public/blog/exit-codes-fallacy</u> directory removed
- <u>/web/tempestphp.com/public/blog/unfair-advantage</u> directory removed
- <u>/web/tempestphp.com/public/blog/alpha-2</u> directory removed
<comment>…</comment>
- <u>/web/tempestphp.com/public/blog/alpha-5</u> directory removed
- <u>/web/tempestphp.com/public/blog/static-websites-with-tempest</u> directory removed

<success>Done</success>
```

It's a pretty cool feature that requires minimal effort, but will have a huge impact on your website's performance. If you want more insights into Tempest's static pages, you can head over to [the docs](/main/features/static-pages) to learn more.

---

<!-- source: src/Web/Blog/articles/2025-03-13-request-objects-in-tempest.md -->

---

title: Request objects in Tempest
description: Why Tempest requests are super intuitive
author: brent
tag: Tutorial

---

Tempest's tagline is "the framework that gets out of your way". One of the best examples of that principle in action is request validation. A pattern I learned to appreciate over the years was to represent "raw data" (like for example, request data), as typed objects in PHP — so-called "data transfer objects". The sooner I have a typed object within my app's lifecycle, the sooner I have a bunch of guarantees about that data, which makes coding a lot easier.

For example: not having to worry about whether the "title of the book" is actually present in the request's body. If we have an object of `BookData`, and that object has a typed property `public string $title` then we don't have to worry about adding extra `isset` or `null` checks, and fallbacks all over the place.

Data transfer objects aren't unheard of in frameworks like [Symfony](https://symfony.com/blog/new-in-symfony-6-3-mapping-request-data-to-typed-objects) or [Laravel](https://spatie.be/docs/laravel-data/v4/introduction), although Tempest takes it a couple of steps further. In Tempest, the starting point of "the request validation flow" is _that_ data object, because _that object_ is what we're _actually_ interested in.

Here's what such a data object looks like:

```php
final class BookData
{
    public string $title;

    public string $description;

    public ?DateTimeImmutable $publishedAt = null;
}
```

It doesn't get much simpler than this, right? We have an object representing the fields we expect from the request. Now how do we get the request data into that object? There are several ways of doing so. I'll start by showing the most verbose way, mostly to understand what's going on. This approach makes use of the `map()` function. Tempest has a built-in [mapper component](/main/features/mapper), which is responsible to map data from one point to another. It could from an array to an object, object to json, one class to another, … Or, in our case: the request to our data object.

Here's what that looks like in practice:

```php
use Tempest\Http\Request;
use function Tempest\map;

final readonly class BookController
{
    #[Post('/books')]
    public function store(Request $request): Redirect
    {
        $bookData = map($request)->to(BookData::class);

        // Do something with that book data
    }
}
```

We have a controller action to store a book, we _inject_ the `Request` class into that action (this class can be injected everywhere when we're running a web app). Next, we map the request to our `BookData` class, and… that's it! We have a validated book object:

```php
/*
 * Book {
 *      title: Timeline Taxi
 *      description: Brent's newest sci-fi novel
 *      publishedAt: 2024-10-01 00:00:00
 * }
 */
```

Now, hang on — _validated_? Yes, that's what I mean when I say that "Tempest gets out of your way": `BookData` uses typed properties, which means we can infer a lot of validation rules from those type signatures alone: `title` and `description` are required since these aren't nullable properties, they should both be text; `publishedAt` is optional, and it expects a valid date time string to be passed via the request.

Tempest infers all this information just by looking at the object itself, without you having to hand-hold the framework every step of the way. There are of course validation attributes for rules that can't be inferred by the type definition itself, but you already get a lot out of the box just by using types.

```php
use Tempest\Validation\Rules\DateTimeFormat;
use Tempest\Validation\Rules\Length;

final class BookData
{
    #[Length(min: 5, max: 50)]
    public string $title;

    public string $description;

    #[DateTimeFormat('Y-m-d')]
    public ?DateTimeImmutable $publishedAt = null;
}
```

This kind of validation also works with nested objects, by the way. Here's for example an `Author` class:

```php
use Tempest\Validation\Rules\Length;
use Tempest\Validation\Rules\Email;

final class Author
{
    #[Length(min: 2)]
    public string $name;

    #[Email]
    public string $email;
}
```

Which can be used on the `Book` class:

```php
final class Book
{
    #[Length(min: 2)]
    public string $title;

    public string $description;

    public ?DateTimeImmutable $publishedAt = null;

    public Author $author;
}
```

Now any request mapped to `Book` will expect the `author.name` and `author.email` fields to be present as well.

## Request Objects

With validation out of the way, let's take a look at other approaches of mapping request data to objects. Since request objects are such a common use case, Tempest allows you to make custom request implementations. There's only a very small difference between a standalone data object and a request object though: a request object implements the `Request` interface. Tempest also provides a `IsRequest` trait that will take care of all the interface-related code. This interface/trait combination is a pattern you'll see all throughout Tempest, it's a very deliberate choice instead of relying on abstract classes, but that's a topic for another day.

Here's what our `BookRequest` looks like:

```php
use Tempest\Http\IsRequest;
use Tempest\Http\Request;

final class BookRequest implements Request
{
    use IsRequest;

    #[Length(min: 5, max: 50)]
    public string $title;

    public string $description;

    // …
}
```

With this request class, we can now simply inject it, and we're done. No more mapping from the request to the data object. Of course, Tempest has taken care of validation as well: by the time you've reached the controller, you're certain that whatever data is present, is also valid.

```php
use function Tempest\map;

final readonly class BookController
{
    #[Post('/books')]
    public function store(BookRequest $request): Redirect
    {
        // Do something with the request
    }
}
```

## Mapping to models

You might be thinking: a request can be mapped to virtually any kind of object. What about models then? Indeed. Requests can be mapped to models directly as well! Let's do some quick setup work.

First we add `database.config.php`, Tempest will discover it, so you can place it anywhere you like. In this example we'll use sqlite as our database:

```php
// app/database.config.php

use Tempest\Database\Config\SQLiteConfig;

return new SQLiteConfig(
    path: __DIR__ . '/database.sqlite'
);
```

Next, create a migration. For the sake of simplicity I like to use raw SQL migrations. You can read more about them [here](/main/essentials/database#migrations). These are discovered as well, so you can place them wherever suits you:

```sql
-- app/Migrations/CreateBookTable.sql

CREATE TABLE `Books`
(
    `id` INTEGER PRIMARY KEY,
    `title` TEXT NOT NULL,
    `description` TEXT NOT NULL,
    `publishedAt` DATETIME
)
```

Next, we'll create a `Book` class, which implements `DatabaseModel` and uses the `IsDatabaseModel` trait:

```php
use Tempest\Database\IsDatabaseModel;

final class Book
{
    use IsDatabaseModel;

    public string $title;

    public string $description;

    public ?DateTimeImmutable $publishedAt = null;
}
```

Then we run our migrations:

```console
~ tempest migrate:up

<em>Migrate up…</em>
- 0000-00-00_create_migrations_table
- CreateBookTable_0

<success>Migrated 2 migrations</success>
```

And, finally, we create our controller class, this time mapping the request straight to the `Book`:

```php
use function Tempest\map;

final readonly class BookController
{
    #[Post('/books')]
    public function store(Request $request): Redirect
    {
        $book = map($request)->to(Book::class);

        $book->save();

        // …
    }
}
```

And that is all! Pretty clean, right?

---

<!-- source: src/Web/Blog/articles/2025-03-16-discovery-explained.md -->

---

title: Tempest's Discovery explained
description: A deep dive into the heart of Tempest.
author: brent
tag: Tutorial

---

At the very core of Tempest lies a concept called "discovery". It's _the_ feature that sets Tempest apart from any other framework. While frameworks like Symfony and Laravel have limited discovery capabilities for convenience, Tempest starts from discovery, and makes into what powers everything else. In this blog post, I'll explain how discovery works, why it's so powerful, and how you can easily build your own.

## How discovery works

The idea of discovery is simple: make the framework understand your code, so that you don't have to worry about configuration or bootstrapping. When we say that Tempest is "the framework that gets out of your way", it's mainly thanks to discovery.

Let's start with an example: a controller action, it looks like this:

```php
use Tempest\Router\Get;
use Tempest\View\View;

final class BookController
{
    #[Get('/books')]
    public function index(): View
    { /* … */ }
}
```

You can place this file anywhere in your project, Tempest will recognise it as a controller action, and register the route into the router. Now, that in itself isn't all that impressive: Symfony, for example, does something similar as well. But let's take a look at some more examples.

Event handlers are marked with the `#[EventHandler]` attribute, the concrete event they handle is determined by the argument type:

```php
use Tempest\EventBus\EventHandler;

final class BooksEventHandlers
{
    #[EventHandler]
    public function onBookCreated(BookCreated $event): void
    {
        // …
    }
}
```

Console commands are discovered based on the `#[ConsoleCommand]` attribute. The console's definition will be generated based on the method definition:

```php
use Tempest\Console\ConsoleCommand;

final readonly class BooksCommand
{
    #[ConsoleCommand]
    public function list(): void
    {
        // ./tempest books:list
    }

    #[ConsoleCommand]
    public function info(string $name): void
    {
        // ./tempest books:info "Timeline Taxi"
    }
}
```

View components are discovered based on their file name:

```html
<!-- x-button.view.php -->

<a :if="isset($href)" class="button" :href="$href">
  <x-slot />
</a>

<div :else class="button">
  <x-slot />
</div>
```

And there are quite a lot more examples. Now, what makes Tempest's discovery different from eg. Symfony or Laravel finding files automatically? Two things:

1. Tempest's discovery works everywhere, literally _everywhere_. There are no specific folders to configure that need scanning, Tempest will scan your whole project, including vendor files — we'll come back to this in a minute.
2. Discovery is made to be extensible. Does your project or package need something new to discover? It's one class and you're done.

These two characteristics make Tempest's discovery really powerful and flexible. It's what allows you to create any project structure you'd like without being told by the framework what it should look like, something many people have said they love about Tempest.

So, how does discovery work? There's are essentially three steps to it:

1. First, Tempest will look at the installed composer dependencies: any project namespace will be included in discovery, and on top of that all packages that require Tempest will be as well.
2. With all the discovery locations determined, Tempest will first scan for classes implementing the `Discovery` interface. That's right: discovery classes themselves are discovered as well.
3. Finally, with all discovery classes found, Tempest will loop through them, and pass each of them all locations to scan. Each discovery class has access to the container, and register whatever it needs to register in it.

As a concrete example, let's take a look at how routes are discovered. Here's the full implementation of `RouteDiscovery`, with some comments added to explain what's going on.

```php
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class RouteDiscovery implements Discovery
{
    use IsDiscovery;

    // Route discovery requires two dependencies,
    // they are both injected via autowiring
    public function __construct(
        private readonly RouteConfigurator $configurator,
        private readonly RouteConfig $routeConfig,
    ) {
    }

    // The `discover` method is called
    // for every possible class that can be discovered
    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        // In case of route registration,
        // we're searching for methods that have a `Route` attribute
        foreach ($class->getPublicMethods() as $method) {
            $routeAttributes = $method->getAttributes(Route::class);

            foreach ($routeAttributes as $routeAttribute) {
                // Each method with a `Route` attribute
                // is stored internally, and will be applied in a second
                $this->discoveryItems->add($location, [$method, $routeAttribute]);
            }
        }
    }

    // The `apply` method is used to register the routes in `RouteConfig`
    // The `discover` and `apply` methods are separate because of caching,
    // we'll talk about it more later in this post
    public function apply(): void
    {
        foreach ($this->discoveryItems as [$method, $routeAttribute]) {
            $route = DiscoveredRoute::fromRoute($routeAttribute, $method);
            $this->configurator->addRoute($route);
        }

        if ($this->configurator->isDirty()) {
            $this->routeConfig->apply($this->configurator->toRouteConfig());
        }
    }
}
```

As you can see, it's not all too complicated. In fact, route discovery is already a bit more complicated because of some route optimizations that need to happen. Here's another example of a very simple discovery implementation, specific to this documentation website (so, a custom one). It's used to discover all classes that implement the `Projector` interface:

```php
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class ProjectionDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly StoredEventConfig $config,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->implements(Projector::class)) {
            $this->discoveryItems->add($location, $class->getName());
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $className) {
            $this->config->projectors[] = $className;
        }
    }
}
```

Pretty simple — right? Even though simple, discovery is really powerful, and sets Tempest apart from any other framework.

## Caching and performance

"Now, hang on. This _cannot_ be performant" — is the first thing I thought when Aidan suggested that Tempest's discovery should scan _all_ project and vendor files. Aidan, by the way, is one of the two other core contributors for Tempest.

Aidan said: "don't worry about it, it'll work". And yes, it does. Although there are a couple of considerations to make.

First, in production, all of this "code scanning" doesn't happen. That's why the `discover()` and `apply()` methods are separated: the `discover()` method will determine whether something should be discovered and prepare it, and the `apply()` method will take that prepared data and store it in the right places. In other words: anything that happens in the `discover()` method will be cached.

Still, that leaves local development though, where you can't cache files because you're constantly working on it. Imagine how annoying it would be if, anytime you added a new controller action, you'd have to clear the discovery cache. Well, true: you cannot cache _project_ files, but you _can_ cache all vendor files: they only update when running `composer up`. This is what's called "partial discovery cache": a caching mode where only vendor discovery is cached and project discovery isn't. Toggling between these modes is done with an environment variable:

```env
{:hl-comment:# .env:}

{:hl-property:DISCOVERY_CACHE:}={:hl-keyword:false:}
{:hl-property:DISCOVERY_CACHE:}={:hl-keyword:true:}
{:hl-property:DISCOVERY_CACHE:}={:hl-keyword:partial:}
```

Now if you're running full or partial discovery cache, there is one more step to take: after deployment or after updating composer dependencies, you'll have to regenerate the discovery cache:

```console
~ ./tempest discovery:generate

  │ <em>Clearing discovery cache</em>
  │ ✔ Done in 132ms.

  │ <em>Generating discovery cache using the all strategy</em>
  │ ✔ Done in 411ms.
```

For local development, the [`tempest/app`](https://github.com/tempestphp/tempest-app) scaffold project already has the composer hook configured for you, and you can easily add it yourself if you made a project without `tempest/app`:

```json
{
  "scripts": {
    "post-package-update": ["@php ./tempest discovery:generate"]
  }
}
```

Oh, one more thing: we did benchmark non-cached discovery performance with thousands of generated files to simulate a real-life project, you can check the source code for those benchmarks [here](https://github.com/tempestphp/tempest-benchmark). The performance impact of discovery on local development was negligible.

That being said, there are improvements we could make to make discovery even more performant. We could, for example, only do real-time discovery on files with actual changes based on the project's git status. These are changes that might be needed in the future, but we won't make any premature optimizations before we've properly tested our current implementation. So if you're playing around with Tempest and running into any performance issues related to discovery, definitely [open an issue](https://github.com/tempestphp/tempest-framework/issues) — that would be very much appreciated!

So, that concludes this dive into discovery. I like to think of it as Tempest's heartbeat. Thanks to discovery, we can ditch most configuration because discovery looks at the code itself and makes decisions based on what's written. It also allows you to structure your project structure any way you want; Tempest won't push you into "controllers go here, models go there".

Do whatever you want, Tempest will figure it out. Why? Because it's **the framework that truly gets out of your way**.

---

<!-- source: src/Web/Blog/articles/2025-03-24-alpha-6.md -->

---

title: The final alpha release
description: Tempest alpha 6 is released, we'll talk about Tempest's future and highlight the most important new features in this release
author: brent
tag: Release

---

Tempest alpha 6 is here: the final alpha release for Tempest. The next one will be beta 1, and from there on out it'll be a straight line to a stable 1.0 release! This final alpha release brings a bunch of new features, improvements, and fixes; this time by 8 contributors in total. I'll walk you through the highlights, but I want to start by talking about the future plans.

```
composer create-project tempest/app:1.0-alpha.6 <name>
```

## Tempest's future

Tempest's first alpha release was tagged half a year ago. It's amazing to see that, since then, 35 people have contributed to the project, and alpha 6 is so different and so much more feature-rich than alpha 1. At the same time, it's important to realize that we cannot stay in alpha for years. There is so much more to be done, and Tempest is far from "ready", but there's a real danger of ending in an infinite "alpha limbo", where we keep adding awesome stuff, but never get to actually release something for real.

I want Tempest to be real. And real things aren't perfect. They don't _have_ to be perfect. That's why we're now moving towards 1.0. There'll be one or two beta releases after this one, but that's it. The goal of these beta releases will be to fix some final bugs, review the docs, do some touch-ups here and there. The goal of 1.0 isn't to be perfect, it's to be real.

There is one thing we've agreed on with the core team: we'll mark some components and features as _experimental_. These experimental features can still change after 1.0 in minor releases. This gives us a bit more freedom to iron out the kinks, but also gives Tempest users some more certainty about what's changing and what not. The goal is to have this list ready before beta.1, and then we'll have some more insights in whether there are possibly future breaking changes or not.

All of that being said, let's talk about what's new in Tempest alpha 6!

## `tempest/view` updates

We start with {`tempest/view`}, which has gotten a lot of love this release. We've fixed a wide range of edge cases and bugs (many were caused because we switched to PHP's built-in HTML 5 spec compliant parser), but we also added a whole range of cool new features.

### `x-template`

There's a new `{html}<x-template>` component which will only render its contents so that you don't have to wrap that content into another element. For example, the following:

```html
<x-template :foreach="$posts as $post">
  <div>{{ $post->title }}</div>
  <span>{{ $post->description }}</span>
</x-template>
```

Will be compiled to:

```html
<div>Post A</div>
<span>Description A</span>
<div>Post B</div>
<span>Description B</span>
<div>Post C</div>
<span>Description C</span>
```

### Dynamic slots and attributes

View components now have direct access to the `$slots` and `$attributes` variables, they give a lot more flexibility when building reusable components.

```html
<x-component name="x-tabs">
  <span :foreach="$attributes['tags'] as $tag">{{ $tag }}</span>

  <x-codeblock :foreach="$slots as $slot">
    <h1>{{ $slot->name }}</h1>

    <h2>{{ $slot->attributes['language'] }}</h2>

    <div>{!! $slot->content !!}</div>
  </x-codeblock>
</x-component>

<x-tabs :tags="['a', 'b', 'c']">
  <x-slot name="php" language="PHP">This is the PHP tab</x-slot>
  <x-slot name="js" language="JavaScript">This is the JS tab</x-slot>
  <x-slot name="html" language="HTML">This is the HTML tab</x-slot>
</x-tabs>
```

### Attribute improvements

Attributes are now more flexible. For example, the `{html}:class` and `{html}:style` expression attributes will be merged automatically with their normal counterpart:

```html
<div class="bg-red-500" :class="$otherClasses"></div>
```

There's support for fallthrough attributes: any `{html}class`, `{html}style` or `{html}id` attribute on a view component will be automatically placed and merged on the first child of that component:

```html
<x-component name="x-with-fallthrough-attributes">
  <div class="bar"></div>
</x-component>

<x-with-fallthrough-attributes class="foo"></x-with-fallthrough-attributes>

<!-- <div class="bar foo"></div> -->
```

### Relative view paths

There's support for relative view paths when returned from controllers:

```php
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View;

final class BookController
{
    #[Get('/books')]
    public function index(): View
    {
        // book_index.view.php can be in the same folder as this directory
        return view('book_index.view.php');
    }
}
```

### View processors

View processors can add data in bulk across multiple views:

```php
use Tempest\View\View;
use Tempest\View\ViewProcessor;

final class StarCountViewProcessor implements ViewProcessor
{
    public function __construct(
        private readonly Github $github,
    ) {}

    public function process(View $view): View
    {
        if (! $view instanceof WithStarCount) {
            return $view;
        }

        return $view->data(starCount: $this->github->getStarCount());
    }
}
```

### File-based view components

View components can now be discovered by file name:

```html
<!-- x-base.view.php -->

<html>
  <head></head>
  <body>
    <x-slot />
  </body>
</html>
```

```html
<x-base> Hello World! </x-base>
```

### The `x-icon` component

And finally, there's a new `{html}<x-icon>` component, added by {gh:nhedger,Nicolas}, which adds built-in support for [Iconify](https://iconify.design/) icons:

```html
<x-icon name="tabler:rss" class="shrink-0 size-4" />
```

## Primitive helpers

{gh:innocenzi,Enzo} has made some pretty significant changes to our `arr()` and `str()` helpers: there are now two variants available: `MutableString` and `ImmutableString`, as well as `MutableArray` and `ImmutableArray`. The helper functions still use the immutable version by default:

```php
use function Tempest\Support\str;

$excerpt = str($content)
    ->excerpt(
        from: $previous->getLine() - 5,
        to: $previous->getLine() + 5,
        asArray: true,
    )
    ->map(function (string $line, int $number) use ($previous) {
        return sprintf(
            "%s%s | %s",
            $number === $previous->getLine() ? '> ' : '  ',
            $number,
            $line
        );
    })
    ->implode(PHP_EOL);
```

We've also made all helper functions available directly as a function:

```php
use function Tempest\Support\Arr\undot;

$data = undot([
    'author.name' => 'Brent',
    'author.email' => 'brendt@stitcher.io',
]);
```

There's also a new `IsEnumHelper` trait which adds a bunch of convenient methods for enums:

```php
use Tempest\Support\IsEnumHelper;

enum MyEnum
{
    use IsEnumHelper;

    case FOO;
    case BAR;
}

MyEnum::FOO->is(MyEnum::BAR);
MyEnum::names();

// …
```

## Mapper improvements

We've changed the API of the mapper slightly to be more consistent. `map()->with()` can now be combined both with `->to()` and `->do()`:

```php
use function Tempest\map;

map($input)->with(BookMapper::class)->to(Book::class);
map($input)->with(BookMapper::class)->do();
```

There are also two new methods to map straight to json and arrays:

```php
use function Tempest\map;

map($book)->toJson();
map($book)->toArray();
```

We also made it possible to add dynamic casters and serializers for non-built in types:

```php
use Tempest\Mapper\Casters\CasterFactory;
use Tempest\Mapper\Casters\SerializerFactory;

$container->get(CasterFactory::class)->addCaster(Carbon::class, CarbonCaster::class);
$container->get(SerializerFactory::class)->addSerializer(Carbon::class, CarbonSerializer::class);
```

## Vite support

{gh:innocenzi,Enzo} has worked hard to add Vite support, with the option to install Tailwind as well. It's as simple as running the Vite installer:

```php
~ ./tempest install vite
```

Next, add `{html}<x-vite-tags />`, in the `{html}<head>` of your template:

```html
<html lang="en" class="h-dvh flex flex-col">
  <head>
    <!-- … -->

    <x-vite-tags />
  </head>
  <body>
    <x-slot />
  </body>
</html>
```

And run your dev server:

```
~ bun run dev

{:hl-comment:# or npm run dev:}
```

Done!

## Database improvements

{gh:blackshadev,Vincent} has simplified database configs, instead of having a single `DatabaseConfig` object with a connection, we've created a `DatabaseConfig` interface, which each driver now implements:

```php
// app/Config/database.config.php

use Tempest\Database\Config\MysqlConfig;
use function Tempest\env;

return new MysqlConfig(
    host: env('DB_HOST'),
    port: env('DB_PORT'),
    username: env('DB_USERNAME'),
    password: env('DB_PASSWORD'),
    database: env('DB_DATABASE'),
);
```

Next, {gh:mattdinthehouse,Matt} added support for a `#[Virtual]` property, which excludes models fields from the model query:

```php
use Tempest\Database\Virtual;
use Tempest\Database\IsDatabaseModel;

class Book
{
    use IsDatabaseModel;

    // …

    public DateTimeImmutable $publishedAt;

    #[Virtual]
    public DateTimeImmutable $saleExpiresAt {
        get => $this->publishedAt->add(new DateInterval('P5D'));
    }
}
```

## New website

One last thing to mention — you might have noticed it already — we've completely redesigned the Tempest website! A big shout-out to {gh:innocenzi,Enzo} who made a huge effort to get it ready! Of course, there a lot more changes with this release, you can check the [full changelog here](https://github.com/tempestphp/tempest-framework/releases/tag/v1.0.0-alpha.6).

## In closing

That's it for this release, I hope you're excited to give Tempest a try, because your input is so valuable. Don't hesitate to [open issues](https://github.com/tempestphp/tempest-framework/issues) and join our [Discord server](https://tempestphp.com/discord), we'd love to hear from you!

---

<!-- source: src/Web/Blog/articles/2025-03-30-about-route-attributes.md -->

---

title: About route attributes
description: Let's explore Tempest's route attributes in depth
author: brent
tag: Thoughts

---

Routing in Tempest is done with route attributes: each controller action can have one or more attributes assigned to them, and each attribute represents a route through which that action is accessible. Here's what that looks like:

```php
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\Router\Delete;
use Tempest\Http\Response;

final class BookAdminController
{
    #[Get('/books')]
    public function index(): Response { /* … */ }

    #[Get('/books/{book}/show')]
    public function show(Book $book): Response { /* … */ }

    #[Post('/books/new')]
    public function new(StoreBookRequest $request): Response { /* … */ }

    #[Post('/books/{book}/update')]
    public function update(BookRequest $bookRequest, Book $book): Response { /* … */ }

    #[Delete('/books/{book}/delete')]
    public function delete(Book $book): Response { /* … */ }
}
```

Not everyone agrees that route attributes are the better solution to configuring routes. I often get questions or arguments against them. However, taking a close look at route attributes reveals that they are superior to big route configuration files or implicit routing based on file names. So let's take a look at each argument against route attributes, and disprove them one by one.

## Route Visibility

The number one argument against route attributes compared to a route configuration file is that routes get spread across multiple files, which makes it difficult to get a global sense of which routes are available. People argue that having all routes listed within a single file is better, because all route configuration is bundled in that one place. Whenever you need to make routing changes, you can find all of them grouped together.

This argument quickly falls apart though. First, every decent framework offers a CLI command to list all routes, essentially giving you an overview of available routes and which controller action they handle. Whether you use route attributes or not, you'll always be able to generate a quick overview list of all routes.

```console
<em>// REGISTERED ROUTES</em>
These routes are registered in your application.

POST /books/new ................................. App\BookAdminController::new
DELETE /books/{book}/delete ..................... App\BookAdminController::delete
GET /books/{book}/show ......................... App\BookAdminController::show
POST /books/{book}/update ....................... App\BookAdminController::update
GET  /books ..................................... App\BookAdminController::index

<comment>…</comment>
```

The second reason this argument fails is that in real project, route files become a huge mess. Thousands of lines of route configuration isn't uncommon in projects, and they are definitely not "easier to comprehend". Moving route configuration and controller actions together actually counteracts this problem, since controllers are often already grouped together in modules, components, sub-folders, … Furthermore, to counteract the problem of "huge routing files", a common practice is to split huge route files into separate parts. In essence, that's exactly what route attributes force you to do by keeping the route attribute as close to the controller action as possible.

## Route Grouping

:::info
Since writing this blog post, route grouping in Tempest has gotten a serious update. Read all about it [here](/blog/route-decorators)
:::

The second-biggest argument against route attributes is the "route grouping" argument. A single route configuration file like for example in Laravel, allows you to reuse route configuration by grouping them together:

```php
Route::middleware([AdminMiddleware::class])
    ->prefix('/admin')
    ->group(function () {
        Route::get('/books', [BookAdminController::class, 'index'])
        Route::get('/books/{book}/show', [BookAdminController::class, 'show'])
        Route::post('/books/new', [BookAdminController::class, 'new'])
        Route::post('/books/{book}/update', [BookAdminController::class, 'update'])
        Route::delete('/books/{book}/delete', [BookAdminController::class, 'delete'])
    });
```

Laravel's approach is really useful because you can configure several routes as a single group, so that you don't have to repeat middleware configuration, prefixes, etc. for _every individual route_. With route attributes, you cannot do that — or can you?

Tempest has a concept called [route decorators](/2.x/essentials/routing#route-decorators-route-groups) which are a super convenient way to model route groups and share behavior. They look like this:

```php
#[{:hl-type:Admin:}, {:hl-type:Books:}]
final class BookAdminController
{
    #[Get('/books')]
    public function index(): View { /* … */ }

    #[Get('/books/{book}/show')]
    public function show(Book $book): View { /* … */ }

    #[Post('/books/new')]
    public function new(): View { /* … */ }

    #[Post('/books/{book}/update')]
    public function update(): View { /* … */ }

    #[Delete('/books/{book}/delete')]
    public function delete(): View { /* … */ }
}
```

You can read more about its design in [this blog post](/blog/route-decorators).

## Route Collisions

One of the few arguments against route attributes that I kind of understand, is how they deal with route collisions. Let's say we have these two routes:

```php
final class BookAdminController
{
    #[Get('/books/{book}')]
    public function show(Book $book): Response { /* … */ }

    #[Get('/books/new')]
    public function new(): Response { /* … */ }
}
```

Here we have a classic collision: when visiting `{txt}/books/new`, the router would detect it as matching the `/books/{book}` route, and, in turn, match the wrong action for that route. Such collisions occur rarely, but I've had to deal with them myself on the odd occasion. The solution, when they occur in the same file, is to simply switch their order:

```php
final class BookAdminController
{
    #[Get('/books/new')]
    public function new(): Response { /* … */ }

    #[Get('/books/{book}')]
    public function show(Book $book): Response { /* … */ }
}
```

This makes it so that `{txt}/books/new` is the first hit, and thus prevents the route collision. However, if these controller actions with colliding routes were spread across multiple files, you wouldn't be able to control their order. So then what?

First of all, there are a couple of ways to circumvent route collisions, using route files or attributes, all the same; that don't require you to rely on route ordering:

- You could change your URI, so that there are no potential collisions: `/books/{book}/show`; or
- you could use regex validation to only match numeric ids: `/books/{book:\d+}`.

Now, as a sidenote: in Tempest, `/books/{book}` and `{txt}/book/new` would never collide, no matter their order. That's because Tempest differentiates between static and dynamic routes, i.e. routes without or with variables. If there's a static route match, it will always get precedence over any dynamic routes that might match. That being said, there are still some cases where route collisions might occur, so it's good to know that, even with route attributes, there are multiple ways of dealing with those situations.

## Performance Impact

The argument of performance impact is easy to refute. People fear that having to scan a whole application to discover route attributes has a negative impact on performance compared to having one route file.

The answer in Tempest's case is simple: discovery is Tempest's core, not just for routing but for everything. It's super performant and properly cached. You can read more about it [here](/blog/discovery-explained).

## File-Based Routing

A completely different approach to route configuration is to simply use the document structure to define routes. So a URI like `/admin/books/{book}/show` would match `App\Controllers\Admin\BooksController::show()`. There are a number of issues file-based routing doesn't address: there's no way to solve the route group issue, you can't configure middleware on a per-route basis, and it's very limiting at scale to have your file structure be defined by the URL scheme.

On the other hand, there's a simplicity to file-based routing that I can appreciate as well.

## Single Responsibility

Finally, the argument that route attributes mix responsibility: a controller action and its route are two separate concerns and shouldn't be mixed in the same file. Personally I feel that's like saying "an id and a model don't belong together", and — to me — that makes no sense. A controller action is nothing without its route, because without its route, that controller action would never be able to run. That's the nature of controller actions: they are the entry points into your application, and for them to be accessible, you _need_ a route.

The best way to show this is to make a controller action. First you create a class and method, and then what? You make a route for it. Isn't it weird that you should go to another file to register the route, only to then return immediately to the controller file to continue your work?

Routes need controllers and controllers need routes. They cannot live without each other, and so keeping them together is the most sensible thing to do.

## Closing Thoughts

I hope it goes without saying, you choose what works best for you. If you decide that route attributes aren't your thing then, well, Tempest won't be your thing. That's ok. I do hope that I was able to present a couple of good arguments in favor of route attributes; and that they might have challenged your opinion if you were absolutely against them.

---

<!-- source: src/Web/Blog/articles/2025-05-08-beta-1.md -->

---

title: Tempest is beta
description: |
Today we release the first beta version of Tempest, the PHP framework for web and console apps that gets out of your way. It's one of the final steps towards a stable 1.0 release. We'll use this beta phase to fix bugs, and we're committed to not making any breaking changes anymore, apart from experimental features.
author: brent
tag: Release

---

Two years ago, Tempest started out as an educational project during one of my livestreams. Since then, we've had 56 people contribute to the framework, merged 591 pull requests, resolved 455 issues, and have written around 50k lines of code. Two contributors joined the core team and dedicated a lot of their time to help make Tempest into something real. And today, we're tagging Tempest as beta.

We have to be real though: we won't get it perfect from the start. Tempest is now in beta, which means we don't plan any breaking changes to stable components anymore, but it also means we expect there to be bugs. And this puts us in an underdog position: why would anyone want to use a framework that has fewer features and likely more bugs than other frameworks?

It turns out, people _do_ see value in Tempest. It's the only reason I decided to work on it in the first place: there is a group of people who _want_ to use it, even when they are aware of its current shortcomings. There is interest in a framework that embraces modern PHP without 10 to 20 years of legacy to carry with it. There is interest in a project that dares to rethink what we've gotten used to over the years. There already is a dedicated community. People already are building with Tempest. Several core members have real use cases for Tempest and are working hard to be able to use it in their own projects as soon as possible. So while Tempest is the underdog, there already seems enough reason for people to use it today.

And I don't want Tempest to remain the underdog. Getting closer to that goal requires getting more people involved. We need hackers to build websites and console applications with Tempest, we need them to run into bugs and edge cases that we haven't thought of. We need entrepreneurs to look into third-party packages, we need to learn what should be improved on our side from their experience. We need you to be involved. That's the next step for Tempest.

Our commitment to you is that we're doing all we can to make Tempest the best developer experience possible. Tempest is and must stay the framework that truly gets out of your way. You need to focus on your code, not on hand-holding and guiding the framework. We're still uncertain about a handful of features and have clearly marked them as [experimental](/main/extra-topics/roadmap), with tried and tested alternatives in place. We're committed to a period of bug fixing to make sure Tempest can be trusted when we release the 1.0 version.

We're committed, and I hope you're intrigued to [give Tempest a go](/main/getting-started/introduction).

```
{:hl-keyword:composer:} create-project:} tempest/app <name>
```

All of that being said, let's look at what's new in this first beta release!

## A truly decoupled ORM

A long-standing issue within Tempest was our ORM: the goal of our model classes was to be truly disconnected from the database, but they weren't really. That's changed in beta.1, where we removed the `DatabaseModel` interface. Any object with typed public properties can now be considered "a model class" by the ORM:

```php
use Tempest\Validation\Rules\Length;
use App\Author;

final class Book
{
    #[Length(min: 1, max: 120)]
    public string $title;

    public ?Author $author = null;

    /** @var \App\Chapter[] */
    public array $chapters = [];
}
```

Now that these model objects aren't tied to the database, they can receive and persistent their data from anywhere, not just a database:

```php
use function Tempest\map;

$books = map($json)->collection()->to(Book::class);

$json = map($books)->toJson();
```

We did decide to keep the `IsDatabaseModel` trait still, because we reckon database persistence is a very common use case:

```php
use Tempest\Database\IsDatabaseModel;

final class Book
{
    use IsDatabaseModel;

    // …
}

$book = Book::create(
    title: 'Timeline Taxi',
    author: $author,
    chapters: [
        new Chapter(index: 1, contents: '…'),
        new Chapter(index: 2, contents: '…'),
        new Chapter(index: 3, contents: '…'),
    ],
);

$books = Book::select()
    ->where('publishedAt > ?', new DateTimeImmutable())
    ->orderBy('title DESC')
    ->limit(10)
    ->with('author')
    ->all();

$books[0]->chapters[2]->delete();
```

However, we also added a new `query()` helper function that can be used instead of the `IsDatabaseModel` trait.

```php
$data = query(Book::class)
    ->select('title', 'index')
    ->where('title = ?', 'Timeline Taxi')
    ->andWhere('index <> ?', '1')
    ->orderBy('index ASC')
    ->all();
```

We've managed to truly decouple model classes from the persistence layer, while still making them really convenient to use. This is a great example of how Tempest gets out of your way.

An important note to make here is that our ORM is one of the few experimental components within Tempest. We acknowledge that there's more work to be done to make it even better, and there might be some future breaking changes still. It's one of the prime examples where we need the community to help us learn what should be improved, and how.

## `tempest/view` changes

We've added support for [dynamic view components](/main/essentials/views#dynamic-view-components), which allows you to render view components based on runtime data:

```html
<!-- $name = 'x-post' -->

<x-component :is="$name" :title="$title" />
```

We've improved [boolean attributes](/main/essentials/views#boolean-attributes), they now also work for truthy and falsy values, as well as for custom expression attributes:

```html
<div :data-active="{$isActive}"></div>

<!-- <div></div> when $isActive is falsy -->
<!-- <div data-active></div> when $isActive is truthy -->
```

Finally, we switched from PHP's built-in DOM parser to our custom implementation. We realized that trying to parse {`tempest/view`} syntax according to the official HTML spec added more problems than it solved. After all, {`tempest/view`} syntax is a superset of HTML: it compiles to spec-compliant HTML, but in itself it is not spec-compliant.

Moving to a custom parser written in PHP comes with a small performance price to pay, but our implementation is slightly more performant than [masterminds/html5](https://github.com/Masterminds/html5-php), the most popular PHP-based DOM parser, and everything our parser does is cached as well. You can [check out the implementation here](https://github.com/tempestphp/tempest-framework/tree/main/packages/view/src/Parser).

## Container features

We've added a new interface called {b`Tempest\Container\HasTag`}, which allows any object to manually specify its container tag. This feature is especially useful combined with config files, and allows you to define multiple config files for multiple occasions. For example, to define multiple database connections:

```php
return new PostgresConfig(
    tag: 'backup',

    // …
);
```

```php
use Tempest\Database\Database;
use Tempest\Container\Tag;

final readonly class BackupService
{
    public function __construct(
        #[Tag('backup')]
        private Database $database,
    ) {}

    // …
}
```

We also added support for proxy dependencies, using PHP 8.4's new object proxies. Any dependency that might be expensive to construct, but not often used, can be injected as a proxy. As a proxy, the dependency will only get resolved when actually needed:

```php
use Tempest\Container\Proxy;

final readonly class BookController
{
    public function __construct(
        #[Proxy]
        private VerySlowClass $verySlowClass
    ) { /* … */ }
}
```

## Middleware discovery

One thing that has felt icky for a long time was that middleware classes could not be discovered (this was the case for all HTTP, console, event bus and command bus middleware). The reason for this restriction was that in some cases, it's important to ensure middleware order: some middleware must come before other, and discovery doesn't guarantee that order. This restriction doesn't match our Tempest mindset, though: we forced all middleware to be manually configured, even though only a small number of middleware classes actually needed that flexibility.

So, as of beta.1, we've added middleware discovery to make the most common case very developer-friendly, and we added the tools necessary to make sure the edge cases are covered as well.

First, you can skip discovery for middleware classes entirely when needed:

```php
use Tempest\Discovery\SkipDiscovery;
use Tempest\Router\HttpMiddleware;

#[SkipDiscovery]
final readonly class ValidateWebhook implements HttpMiddleware
{
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        // …
    }
}
```

And, second, you can define middleware priority for specific classes to ensure the right order when needed:

```php
use Tempest\Core\Priority;

#[Priority(Priority::HIGHEST)]
final readonly class OverviewMiddleware implements ConsoleMiddleware
{
    public function __invoke(Invocation $invocation, ConsoleMiddlewareCallable $next): ExitCode|int
    {
        // …
    }
}
```

## Smaller features

Finishing with a couple of smaller changes, but it's these kinds of small details that make the difference in the long run. So thanks to everyone who contributed:

- We've added a couple of new commands: `make:migration` and `container:show`
- We've added testing utilities for our [event bus](/main/features/events)
- There's a new `Back` response class to redirect to the previous page
- We now allow controllers to also return strings and arrays directly
- We've added a [new storage component](/main/features/file-storage), which is a slim wrapper around [Flysystem](https://flysystem.thephpleague.com/docs/)
- And, [a lot more](https://github.com/tempestphp/tempest-framework/releases/tag/v1.0.0-beta.1)

## In closing

It's amazing to see what we've achieved in a little less than two years. Tempest has grown from being a dummy project used during livestreams, to a real framework.

There's a long way to go still, but I'm confident when I see how many people are contributing to and excited about Tempest. You can follow along the beta progress on [GitHub](https://github.com/tempestphp/tempest-framework/milestone/16); and you can be part of the journey as well: [give Tempest a try](/main/getting-started/getting-started) and [join our Discord server](https://tempestphp.com/discord).

See you soon!

<img class="w-[1.66em] shadow-md rounded-full" src="/tempest-logo.png" alt="Tempest" />

---

<!-- source: src/Web/Blog/articles/2025-05-26-tempests-vision.md -->

---

title: Tempest's vision
description: What sets Tempest apart as a framework for modern PHP development.
author: brent
tag: Thoughts
meta:
canonical: https://tempestphp.com/main/getting-started/introduction

---

Today I want to share a bit of Tempest's vision. People often ask about the "why" of building a new framework, and so I wanted to take some time to properly think and write down my thoughts.

I tried to summarize Tempest's vision in one sentence, and came up with this: **Tempest is a community-driven, modern PHP framework that gets out of your way and dares to think outside the box**.

There's a lot packed in one sentence though, so let's go through it in depth.

## Community driven

Tempest started out as an educational project, without the intention for it to be something real. People picked up on it, though, and it was only after a strong community had formed that we considered making it anything else but a thought exercise.

Currently, there are three core members dedicating time to Tempest, as well as over [50 additional contributors](https://github.com/tempestphp/tempest-framework). We have an active [Discord server](/discord) with close to 400 members.

Tempest isn't a solo project and never has been. It is a new framework and has a way to go compared to Symfony or Laravel, but there already is significant momentum and will only keep growing.

## Embracing modern PHP

The benefit of starting from scratch like Tempest did is having a clean slate. Tempest embraced modern PHP features from the start, and its goal is to keep doing this in the future by shipping built-in upgraders whenever breaking changes happen (think of it as Laravel Shift, but built into the framework).

Just to name a couple of examples, Tempest uses property hooks:

```php
interface DatabaseMigration
{
    public string $name {
        get;
    }

    public function up(): ?QueryStatement;

    public function down(): ?QueryStatement;
}
```

Attributes:

```php
final class BookController
{
    #[Get('/books/{book}')]
    public function show(Book $book): Response { /* … */ }
}
```

Proxy objects:

```php
use Tempest\Container\Proxy;

final readonly class BookController
{
    public function __construct(
        #[Proxy] private SlowDependency $slowDependency,
    ) { /* … */ }
}
```

And a lot more.

## Getting out of your way

A core part of Tempest's philosophy is that it wants to "get out of your way" as best as possible. For starters, Tempest is designed to structure project code however you want, without making any assumptions or forcing conventions on you. You can prefer a classic MVC application, DDD or hexagonal design, microservices, or something else; Tempest works with any project structure out of the box without any configuration.

Behind Tempest's flexibility is one of its most powerful features: [discovery](/main/internals/discovery). Discovery gives Tempest a great number of insights into your codebase, without any handholding. Discovery handles routing, console commands, view components, event listeners, command handlers, middleware, schedules, migrations, and more.

```php
final class ConsoleCommandDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly ConsoleConfig $consoleConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            if ($consoleCommand = $method->getAttribute(ConsoleCommand::class)) {
                $this->discoveryItems->add($location, [$method, $consoleCommand]);
            }
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$method, $consoleCommand]) {
            $this->consoleConfig->addCommand($method, $consoleCommand);
        }
    }
}
```

Discovery makes Tempest truly understand your codebase so that you don't have to explain the framework how to use it. Of course, discovery is heavily optimized for local development and entirely cached in production, so there's no performance overhead. Even better: discovery isn't just a core framework feature, you're encouraged to write your own project-specific discovery classes wherever they make sense. That's the Tempest way.

Besides Discovery, Tempest is designed to be extensible. You'll find that any part of the framework can be replaced and hooked into by implementing an interface and plugging it into the container. No fighting the framework, Tempest gets out of your way.

```php
use Tempest\View\ViewRenderer;

$container->singleton(ViewRenderer::class, $myCustomViewRenderer);
```

## Thinking outside the box

Finally, since Tempest originated as an educational project, many Tempest features dare to rethink the things we've gotten used to. For example, [console commands](/main/1-essentials/04-console-commands), which in Tempest are designed to be very similar to controller actions:

```php
final readonly class BooksCommand
{
    use HasConsole;

    public function __construct(
        private BookRepository $repository,
    ) {}

    #[ConsoleCommand]
    public function find(?string $initial = null): void
    {
        $book = $this->search(
            'Find your book',
            $this->repository->find(...),
        );
    }

    #[ConsoleCommand(middleware: [CautionMiddleware::class])]
    public function delete(string $title, bool $verbose = false): void
    { /* … */ }
}
```

Or what about [Tempest's ORM](/main/1-essentials/03-database), which aims to have truly decoupled models:

```php
use Tempest\Validation\Rules\Length;
use App\Author;

final class Book
{
    #[Length(min: 1, max: 120)]
    public string $title;

    public ?Author $author = null;

    /** @var \App\Chapter[] */
    public array $chapters = [];
}
```

```php
final class BookRepository
{
    public function findById(int $id): Book
    {
        return query(Book::class)
            ->select()
            ->with('chapters', 'author')
            ->where('id = ?', $id)
            ->first();
    }
}
```

Then there's our view engine, which embraces the most original template engine of all time: HTML;

```html
<x-base :title="$this->seo->title">
  <ul>
    <li :foreach="$this->books as $book">
      {{ $book->title }}

      <span :if="$this->showDate($book)">
        <x-tag> {{ $book->publishedAt }} </x-tag>
      </span>
    </li>
  </ul>
</x-base>
```

---

So, those are the four main pillars of Tempest's vision:

- Community-driven
- Modern PHP
- Getting out of your way
- Thinking outside the box

People who use Tempest say it's the sweet spot between the robustness of Symfony and the eloquence of Laravel. It feels lightweight and close to vanilla PHP; and yet powerful and feature-rich.

But, you shouldn't take my word for it. I'd encourage you to [give Tempest a try](/main/getting-started/installation).

---

<!-- source: src/Web/Blog/articles/2025-06-27-tempest-1.md -->

---

title: Tempest 1.0
description: Tempest's first stable release
author: brent
tag: Release

---

After almost 2 years and 656 merged pull requests by 59 contributors, it is finally time to tag the first release of Tempest. In case you don't know: Tempest is a framework for web and console application development. [It's community-driven, embraces modern PHP, gets out of your way, and dares to think outside the box](/blog/tempests-vision). There is so much to tell about Tempest, but I think code says more than words, so let me share a few highlights that I personally am excited about.

[A truly decoupled ORM](/main/essentials/database); this is what model classes look like in Tempest:

```php
use Tempest\Validation\Rules\Length;
use App\Author;

final class Book
{
    #[Length(min: 1, max: 120)]
    public string $title;

    public ?Author $author = null;

    /** @var \App\Chapter[] */
    public array $chapters = [];
}

$book = query(Book::class)
    ->select()
    ->with('chapters', 'author')
    ->where('id = ?', $id)
    ->first();
```

[A powerful templating engine](/main/essentials/views); which builds on top of the OG-templating engine of all time — HTML:

```html
<x-base :title="$this->seo->title">
  <ul>
    <li :foreach="$this->books as $book">
      {{ $book->title }}

      <span :if="$this->showDate($book)">
        <x-tag> {{ $book->publishedAt }} </x-tag>
      </span>
    </li>
  </ul>
</x-base>
```

[Reimagined console applications](/main/essentials/console-commands); making console programming with PHP super intuitive:

```php
final readonly class BooksCommand
{
    use HasConsole;

    public function __construct(
        private BookRepository $repository,
    ) {}

    #[ConsoleCommand]
    public function find(): void
    {
        $book = $this->search(
            'Find your book',
            $this->repository->find(...),
        );
    }

    #[ConsoleCommand(middleware: [CautionMiddleware::class])]
    public function delete(string $title, bool $verbose = false): void
    { /* … */ }
}
```

[Discovery](/blog/discovery-explained); which makes Tempest truly understand your code — no handholding required:

```php
final class ConsoleCommandDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly ConsoleConfig $consoleConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            if ($consoleCommand = $method->getAttribute(ConsoleCommand::class)) {
                $this->discoveryItems->add($location, [$method, $consoleCommand]);
            }
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$method, $consoleCommand]) {
            $this->consoleConfig->addCommand($method, $consoleCommand);
        }
    }
}
```

Or what about [the mapper](/main/features/mapper), [command bus](/main/features/command-bus), [events](/main/features/events), [logging](/main/features/logging), [caching](/main/features/cache), [localization](/main/features/localization), [scheduling](/main/features/scheduling), [validation](/main/features/validation), and even more.

There is a lot to tell about Tempest, and honestly, I'm so proud of what a small but very talented community has managed to achieve. When I started Tempest 2 years ago, the goal was for it to be an educational project, nothing more. But people stepped in. They liked the direction of this framework so much, eventually leading to where we are today.

And you might wonder: where does Tempest fit in, in an age where we have mature frameworks like Symfony and Laravel? Well: tagging 1.0 is only the beginning, and there is so much more to be done. At the same time, so many people have tried Tempest and said they like it a lot. It's simple, modern, intuitive, there's no legacy to be dealt with. Developers like Tempest.

I remember the first Reddit posts announcing Laravel, more than a decade ago; people were so skeptical of something new. And yet, see where Laravel is today. I believe there's room for Tempest to continue to grow, and I would say this is the perfect time to get started with it.

If you're ready to give it a try, head over to [the docs](/main/getting-started/installation), and [join our Discord server](https://tempestphp.com/discord) to get started!

---

<!-- source: src/Web/Blog/articles/2025-06-29-ten-tempest-tips.md -->

---

title: Ten Tempest Tips
description: "Ten things you might now know about Tempest"
author: brent
tag: Thoughts

---

With the release of Tempest 1.0, many people wonder what the framework is about. There is so much to talk about, and I decided to highlight a couple of features in this blog post. I hope it might intrigue you to give Tempest a try, and discover even more!

## 1. Make it your own

Tempest is designed with the flexibility to structure your projects whatever way you want. You can choose a classic MVC project, a DDD-inspired approach, hexagonal design, or anything else that suits your needs, without any configuration or framework adjustments. It just works the way you want.

```txt
.                                    .
└── src                              └── app
    ├── Authors                          ├── Controllers
    │   ├── Author.php                   │   ├── AuthorController.php
    │   ├── AuthorController.php         │   └── BookController.php
    │   └── authors.view.php             ├── Models
    ├── Books                            │   ├── Author.php
    │   ├── Book.php                     │   ├── Book.php
    │   ├── BookController.php           │   └── Chapter.php
    │   ├── Chapter.php                  ├── Services
    │   └── books.view.php               │   └── PublisherGateway.php
    ├── Publishers                       └── Views
    │   └── PublisherGateway.php             ├── authors.view.php
    └── Support                              ├── books.view.php
        └── x-base.view.php                  └── x-base.view.php
```

## 2. Discovery

The mechanism that allows such a flexible project structure is called [Discovery](/blog/discovery-explained). With Discovery, Tempest will scan your whole project and infer an incredible amount of information by reading your code, so that you don't have to configure the framework manually. On top of that, Tempest's discovery is designed to be extensible for project developers and package authors.

For example, I built a small event-sourcing implementation to keep track of website analytics [on this website](https://github.com/tempestphp/tempest-docs/blob/main/src/StoredEvents/ProjectionDiscovery.php). For that, I wanted to discover event projections within the app. Instead of manually listing classes in a config file somewhere. So I hooked into Tempest's discovery flow, which only requires implementing a single interface:

```php
final class ProjectionDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly StoredEventConfig $config,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->implements(Projector::class)) {
            $this->discoveryItems->add($location, $class->getName());
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $className) {
            $this->config->projectors[] = $className;
        }
    }
}
```

Of course, Tempest comes with a bunch of discovery implementations built in: routes, console commands, middleware, view components, event and command handlers, migrations, other discovery classes, and more. You can [read more about discovery here](/blog/discovery-explained).

## 3. Config classes

[Configuration](/docs/essentials/configuration#configuration-files) in Tempest is handled via classes. Any component that needs configuration will have one or more config classes. Config classes are simple data objects and don't require any setup. They might look something like this:

```php

final class MysqlConfig implements DatabaseConfig
{
    public string $dsn {
        get => sprintf(
            'mysql:host=%s:%s;dbname=%s',
            $this->host,
            $this->port,
            $this->database,
        );
    }

    public DatabaseDialect $dialect {
        get => DatabaseDialect::MYSQL;
    }

    public function __construct(
        #[SensitiveParameter]
        public string $host = 'localhost',
        #[SensitiveParameter]
        public string $port = '3306',
        #[SensitiveParameter]
        public string $username = 'root',
        #[SensitiveParameter]
        public string $password = '',
        #[SensitiveParameter]
        public string $database = 'app',
        // …
    ) {}
}
```

The first benefit of config classes is that the configuration schema is defined with class properties, which means you'll have proper static insight when defining and using configuration within Tempest:

```php database.config.php
use Tempest\Database\Config\MysqlConfig;
use function Tempest\env;

return new MysqlConfig(
    host: env('DB_HOST'),
    post: env('DB_PORT'),
    username: env('DB_USERNAME'),
    password: env('DB_PASSWORD'),
);
```

The second benefit of config classes is that their instances are discovered and registered in the container. Whenever a file ends with `.config.php` and returns a new config object, then that config object will be available via autowiring throughout your code:

```php app/stored-events.config.php
use App\StoredEvents\StoredEventConfig;

return new StoredEventConfig();
```

```php app/StoredEvents/EventsReplayCommand.php
use App\StoredEvents\StoredEventConfig;

final readonly class EventsReplayCommand
{
    public function __construct(
        private StoredEventConfig $storedEventConfig,
        // …
    ) {}
}
```

## 4. Static pages

Tempest has built-in support for generating [static websites](/blog/static-websites-with-tempest). The idea is simple: why boot the framework when all that's needed is the same HTML page for any request to a specific URI? All you need is to mark an existing controller with the `#[StaticPage]` attribute, optionally add a data provider for dynamic routes, and you're set:

```php
use Tempest\Router\StaticPage;

final readonly class BlogController
{
    // …

    #[StaticPage(BlogDataProvider::class)]
    #[Get('/blog/{slug}')]
    public function show(string $slug, BlogRepository $repository): Response|View
    {
        // …
    }
}
```

Finally, all you need to do is run the `{console}static:generate` command, and your static website is ready:

```console
~ tempest static:generate

- <u>/blog</u> > <u>/web/tempestphp.com/public/blog/index.html</u>
- <u>/blog/exit-codes-fallacy</u> > <u>/web/tempestphp.com/public/blog/exit-codes-fallacy/index.html</u>
- <u>/blog/unfair-advantage</u> > <u>/web/tempestphp.com/public/blog/unfair-advantage/index.html</u>
- <u>/blog/alpha-2</u> > <u>/web/tempestphp.com/public/blog/alpha-2/index.html</u>
<comment>…</comment>
- <u>/blog/alpha-5</u> > <u>/web/tempestphp.com/public/blog/alpha-5/index.html</u>
- <u>/blog/static-websites-with-tempest</u> > <u>/web/tempestphp.com/public/blog/static-websites-with-tempest/index.html</u>

<success>Done</success>
```

## 5. Console arguments

Console commands in Tempest require as little configuration as possible, and will be defined by the handler method's signature. Once again thanks to discovery, Tempest will infer what kind of input a console command needs, based on the [method's argument list](/docs/essentials/console-commands#command-arguments):

```php
final readonly class EventsReplayCommand
{
    // …

    #[ConsoleCommand]
    public function __invoke(?string $replay = null, bool $force = false): void
    { /* … */ }
}

// ./tempest events:replay PackageDownloadsPerDayProjector --force
```

## 6. Response classes

While Tempest has a generic response class that can be returned from controller actions, you're encouraged to use one of the specific response implementations instead:

```php
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Responses\Download;

final class DownloadController
{
    #[Get('/downloads')]
    public function index(): Response
    {
        // …

        return new Ok(/* … */);
    }

    #[Get('/downloads/{id}')]
    public function download(string $id): Response
    {
        // …

        return new Download($path);
    }
}
```

Making your own response classes is trivial as well: you must implement the `Tempest\Http\Response` interface and you're ready to go. For convenience, there's also an `IsResponse` trait:

```php
use Tempest\Http\Response
use Tempest\Http\IsResponse;

final class BookCreated implements Response
{
    use IsResponse;

    public function __construct(Book $book)
    {
        $this->setStatus(\Tempest\Http\Status::CREATED);
        $this->addHeader('x-book-id', $book->id);
    }
}
```

## 7. SQL migrations

Tempest has a database migration builder to manage your database's schema:

```php
use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

final class CreateBookTable implements DatabaseMigration
{
    public string $name = '2024-08-12_create_book_table';

    public function up(): QueryStatement|null
    {
        return new CreateTableStatement('books')
            ->primary()
            ->text('title')
            ->datetime('created_at')
            ->datetime('published_at', nullable: true)
            ->integer('author_id', unsigned: true)
            ->belongsTo('books.author_id', 'authors.id');
    }

    public function down(): QueryStatement|null
    {
        return new DropTableStatement('books');
    }
}
```

But did you know that Tempest also supports raw SQL migrations? Any `.sql` file within your application directory will be discovered automatically:

```sql app/Migrations/2025-01-01_create_publisher_table.sql
CREATE TABLE Publisher
(
    `id`   INTEGER,
    `name` TEXT NOT NULL
);
```

## 8. Console middleware

You might know middleware as a concept for HTTP requests, but Tempest's console also supports middleware. This makes it easy to add reusable functionality to multiple console commands. For example, Tempest comes with a `CautionMiddleware` and `ForceMiddleware` built-in. These middlewares add an extra warning before executing the command in production, and an optional `--force` flag to skip these kinds of warnings.

```php
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Middleware\ForceMiddleware;
use Tempest\Console\Middleware\CautionMiddleware;

final readonly class EventsReplayCommand
{
    #[ConsoleCommand(middleware: [ForceMiddleware::class, CautionMiddleware::class])]
    public function __invoke(?string $replay = null): void
    { /* … */ }
}
```

You can also make your own console middleware, you can [find out how here](/docs/essentials/console-commands#middleware).

## 9. Interfaces everywhere

When you're diving into Tempest's internals, you'll notice how we prefer to use interfaces over abstract classes. The idea is simple: if there's something framework-related to hook into, you'll be able to implement an interface and register your own implementation in the container. Most of the time, you'll also find a default trait implementation. There's a good reason behind this design, and you can read all about it [here](https://stitcher.io/blog/extends-vs-implements).

## 10. Initializers

Finally, let's talk about [dependency initializers](/docs/essentials/container#dependency-initializers). Initializers are tasked with setting up one or more dependencies in the container. Whenever you need a complex dependency available everywhere, your best option is to make a dedicated initializer class for it. Here's an example: setting up a Markdown converter that can be used throughout your app:

```php
use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class MarkdownInitializer implements Initializer
{
    public function initialize(Container $container): MarkdownConverter
    {
        $environment = new Environment();
        $highlighter = new Highlighter(new CssTheme());

        $highlighter
            ->addLanguage(new TempestViewLanguage())
            ->addLanguage(new TempestConsoleWebLanguage())
            ->addLanguage(new ExtendedJsonLanguage());

        $environment
            ->addExtension(new CommonMarkCoreExtension())
            ->addExtension(new FrontMatterExtension())
            ->addRenderer(FencedCode::class, new CodeBlockRenderer($highlighter))
            ->addRenderer(Code::class, new InlineCodeBlockRenderer($highlighter));

        return new MarkdownConverter($environment);
    }
}
```

As with most things-Tempest, they are discovered automatically. Creating an initializer class and setting the right return type for the `initialize()` method is enough for Tempest to pick it up and set it up within the container.

## There's a lot more!

To truly appreciate Tempest, you'll have to write code with it. To get started, head over to [the documentation](/docs/getting-started/installation) and [join our Discord server](/discord)!

---

<!-- source: src/Web/Blog/articles/2025-07-05-tempest-1-1.md -->

---

title: Tempest 1.1 released
description: A new minor version is available
author: brent
tag: Release

---

It's been a little over a week since Tempest was released. It's great to see so many people have [joined the Discord server](/discord), created issues and feature requests, and sent PRs! Today we're tagging the first minor release which includes a range of bugfixes, as well as some new features. Let's take a look!

## Database seeders

This release adds support for [database seeders](/docs/essentials/database#database-seeders), which allow you to fill your database with dummy data for local development. The only thing you need is a class implementing the {`\Tempest\Database\DatabaseSeeder`} interface, which Tempest will then discover:

```console
./tempest database:seed

 │ <em>Which seeders do you want to run?</em>
 │ / <dim>Filter...</dim>
 │ → ⋅ Tests\Tempest\Fixtures\MailingSeeder
 │   ⋅ Tests\Tempest\Fixtures\InvoiceSeeder
```

Note how you can create multiple seeders and select them when running the `database:seed` command. Multiple seeders are especially useful when you have larger applications where you want the ability to bring the database to specific states, depending on which feature you're working on.

Database seeding also works with the `migrate:fresh` command, supports multiple databases, and more. You can read all about them [here](/docs/essentials/database#database-seeders).

## Discovery improvements

We made an effort to [improve discovery performance](https://github.com/tempestphp/tempest-framework/pull/1333), increasing non-cached and partial performance with ~30%. Together with [config cache improvements](https://github.com/tempestphp/tempest-framework/pull/1341), running Tempest locally feels very snappy now. As a reference point, we used this documentation website, which now takes between 100ms and 200ms to load (it used to be between 400ms and 600ms). Keep in mind these numbers though may vary depending on your machine. Overall, there's a clear performance improvement though, and we're really happy with that.

If you happen to run into any issues after updating to 1.1, please let us know [on Discord](/discord) or [via GitHub](https://github.com/tempestphp/tempest-framework). The upgrade should be as easy as running `composer up`, but if you do encounter errors, we'd like to know so that we can fix them.

## Smaller features and bug fixes

There were also a bunch of smaller features and bug fixes added in this release:

- [A new `HexColor` validation rule](https://github.com/tempestphp/tempest-framework/pull/1332)
- [A new session `reflash()` method](https://github.com/tempestphp/tempest-framework/pull/1338)
- [The ability to only specify a port when running `tempest serve`](https://github.com/tempestphp/tempest-framework/pull/1350)
- [Support implicit HEAD requests](https://github.com/tempestphp/tempest-framework/pull/1349)
- [Fix log level-specific drivers](https://github.com/tempestphp/tempest-framework/pull/1343)
- [Enable icon cache by default](https://github.com/tempestphp/tempest-framework/pull/1339)
- [And more](https://github.com/tempestphp/tempest-framework/releases/tag/v1.1.0)

## What's next?

We aim to release a new minor version every one to two weeks. We're currently working on the [new email component](https://github.com/tempestphp/tempest-framework/pull/1227), [redis support](https://github.com/tempestphp/tempest-framework/pull/1252), [a wrapper for symfony/process](https://github.com/tempestphp/tempest-framework/pull/1326), discussing oauth support, and more.

As always: you're welcome to join the Tempest community to help shape the future of the framework. The best place to start is by [joining our Discord server](/discord).

---

<!-- source: src/Web/Blog/articles/2025-07-17-mail-component.md -->

---

title: Mailing with Tempest
description: The newest Tempest release adds mailing support
author: brent
tag: Release

---

Mailing is a pretty crucial feature for many apps, and I'm happy that we tagged Tempest 1.4 today, which includes mailing support. We didn't invent mailing from scratch though, we decided to build on top of the excellent Mailer component provided by Symfony (including all of its transport drivers) and build a small layer on top of those that fits well within Tempest.

Here's what an email looks like in Tempest:

```php
use Tempest\Mail\Attachment;
use Tempest\Mail\Email;
use Tempest\Mail\Envelope;
use Tempest\Mail\HasAttachments;
use Tempest\View\View;
use function Tempest\view;

final class WelcomeEmail implements Email, HasAttachments
{
    public function __construct(
        private readonly User $user,
    ) {}

    public Envelope $envelope {
        get => new Envelope(
            subject: 'Welcome',
            to: $this->user->email,
        );
    }

    public string|View $html {
        get => view('welcome.view.php', user: $this->user);
    }

    public array $attachments {
        get => [
            Attachment::fromFilesystem(__DIR__ . '/welcome.pdf')
        ];
    }
}
```

And here is how you'd use it:

```php
use Tempest\Mail\Mailer;
use Tempest\Mail\GenericEmail;

final class UserEventHandlers
{
    public function __construct(
        private readonly Mailer $mailer,
    ) {}

    #[EventHandler]
    public function onCreated(UserCreated $userCreated): void
    {
        $this->mailer->send(new WelcomeEmail($userCreated->user));

        $this->success('Done');
    }
}
```

We have built-in support for SMTP, Amazon SES, and Postmark; as well as the ability to add any transport you'd like, as long as there's a Symfony driver for it. Next, we have convenient testing helpers:

```php
public function test_welcome_mail()
{
    $this->mailer
        ->send(new WelcomeEmail($this->user))
        ->assertSentTo($this->user->email)
        ->assertAttached('welcome.pdf');
}
```

And a lot of other niceties you can discover in [the docs](/docs/features/mail).

Finally, we're playing with a handful of ideas for future improvements as well. For example, tagging emails as `#[AsyncEmail]`, which would automatically send them to our async command bus and handle them in the background:

```php
// Work in progress!

#[AsyncEmail]
final class WelcomeEmail implements Email, HasAttachments
{ /* … */ }
```

And there's also an idea to model emails as views, instead of PHP classes:

```php
$mailer->send('welcome.view.php', user: $user);
```

```html welcome.view.php
<!-- Work in progress! -->

<x-email subject="Welcome!" :to="$user->to">
  <h1>Welcome {{ $user->name }}!</h1>

  <p>
    Please activate your account by visiting this link: {{ $user->activationLink
    }}
  </p>
</x-email>
```

Mailing is the first big feature we release after Tempest 1.0. We decided to mark all new features as experimental for a couple of releases. This gives us the opportunity to fix any oversights there might be with the design we came up with. Because, let's be real: we're not perfect, and we rarely write code that's perfect from the get-go. We hope that enough enthusiasts will try out our new mailing component though, and provide us with the feedback we need to make it even better. If you want to know how to do that, then [Discord](/discord) is the place to be!

---

<!-- source: src/Web/Blog/articles/2025-07-28-tempest-view-updates.md -->

---

title: Major updates to Tempest views
description: Tempest 1.5 released with some major improvements to its templating engine
author: brent
tag: Release

---

Today we released Tempest version 1.5, which includes a bunch of improvements to [Tempest View](/docs/essentials/views), the templating engine that ships by default with the framework. Tempest also has support for Blade and Twig, but we designed Tempest View to take a unique approach to templating with PHP, and I must say: it looks excellent! (I might be biased.)

Designing a new language is hard, even if it's "only" a templating language, which is why we marked Tempest View as experimental when Tempest 1.0 released. This meant the package could still change over time, although we try to keep breaking changes at a minimum.

With the release of Tempest 1.5, we did have to make a handful of breaking changes, but overall they shouldn't have a big impact. And I believe both changes are moving the language forward in the right direction. In this post, I want to highlight the new Tempest View features and explain why they needed a breaking change or two.

Let's take a look!

## Scoped variables

The first change has to do with view component variable scoping. We didn't properly handle variable scoping before, which often lead to leaked variables into the wrong scope. That has now been solved though, and variable scoping now follows almost exactly the same rules as normal PHP closures would.

With these changes, local variables defined within a view component cannot be leaked to the outer scope anymore:

```html
<x-post>
  <?php $title = str($post->title)->title(); ?>

  <h1>{{ $title }}</h1>
</x-post>

<!-- $title won't be available outside the view component. -->
```

And likewise, view components won't have access to variables from the outer scope, unless explicitly passed in:

```html
<!-- $title will need to be passed in explicitly: -->

<x-post :title="$title"></x-post>
```

There's one exception to this rule: variables defined by the view itself are directly accessible from within view components. This can be useful when you're using view components that are tied to one specific view, but extracted to a component to avoid code repetition.

:::code-group

```html x-home-highlight.view.php
<div class="<!-- … -->">{!! $highlights[$name] !!}</div>

<!-- in home.view.php -->
<x-home-highlight name="orm" />
```

```php app/HomeController.php
final class HomeController
{
    #[Get('/')]
    public function __invoke(HighlightRepository $highlightRepository): View
    {
        return view(
            './home.view.php',
             highlights: $highlightRepository->all(),
         );
    }
}
```

:::

Variable scoping now works by compiling view components to PHP closures instead of what we used to do: manage variable scope ourselves. Besides fixing some bugs, it also [simplified view component rendering significantly](https://github.com/tempestphp/tempest-framework/pull/1435), which is great!

## Installable view components

The second feature made some changes to view component discovery. We now have an installation command for components: you can use a selection of built-in components that ship with the framework like `{html}<x-markdown />`, `{html}<x-icon />`, `{html}<x-input />`, etc.; but you can also publish those components into your project. This means that, for quick prototyping, you can use the built-in components without any setup; and for real projects, you can publish the necessary components to style and change them to your liking.

```console
./tempest install view-components

 <dim>│</dim> <em>Select which view components you want to install</em>
 <dim>│</dim> / <dim>Filter...</dim>
 <dim>│</dim> → ⋅ x-csrf-token
 <dim>│</dim>   ⋅ x-markdown
 <dim>│</dim>   ⋅ x-input
 <dim>│</dim>   ⋅ x-icon

<comment>…</comment>
```

This installation process will hook into _any_ third party package, by the way; so it will be trivial to make a third-party frontend component library, for example, Tempest's discovery will be doing the heavy lifting for you.

This feature came with a [pretty significant refactoring](https://github.com/tempestphp/tempest-framework/pull/1439). In order to keep the code clean, we decided to remove a bunch of old and undocumented features. The most significant one is that the `ViewComponent` interface is no more, and all view components must now be handled via their view files. Here's, for example, what the `{html}<x-input />` view component's source looks like:

```html
<?php
/**
 * @var string $name
 * @var string|null $label
 * @var string|null $id
 * @var string|null $type
 * @var string|null $default
 */

use Tempest\Http\Session\Session;

use function Tempest\get;
use function Tempest\Support\str;

/** @var Session $session */
$session = get(Session::class);

$label ??= str($name)->title(); $id ??= $name; $type ??= 'text'; $default ??=
null; $errors = $session->getErrorsFor($name); $original =
$session->getOriginalValueFor($name, $default); ?>

<div>
  <label :for="$id">{{ $label }}</label>

  <textarea :if="$type === 'textarea'" :name="$name" :id="$id">
{{ $original }}</textarea
  >
  <input :else :type="$type" :name="$name" :id="$id" :value="$original" />

  <div :if="$errors !== []">
    <div :foreach="$errors as $error">{{ $error->message() }}</div>
  </div>
</div>
```

While this style might require some getting used to for some people, I think it is the right decision to make: class-based view components had a lot of compiler edge cases that we had to take into account, and often lead to subtle bugs when building new components. I do plan on writing an in-depth post on how to build reusable view components with Tempest soon. Stay tuned for that!

## Work in progress IDE support

Then, the final (very much WORK IN PROGRESS) feature: Nicolas and Márk have stepped up to build an [LSP for Tempest](https://github.com/nhedger/tempest-ls), as well as plugins for [PhpStorm](https://plugins.jetbrains.com/plugin/27971-tempest) and [VSCode](https://marketplace.visualstudio.com/items?itemName=nhedger.tempest).

There is a lot of work to be done, but it's amazing to see this moving forward. If you want to get involved, definitely [join our Discord server](/discord), and you can also check out the [Tempest View specification](/docs/internals/view-spec) to learn more about the language itself.

## All breaking changes listed

- `{html}<x-csrf-token />` must now be added to all forms ([#1411](https://github.com/tempestphp/tempest-framework/pull/1411)).
- View component variables must be passed explicitly ([#1435](https://github.com/tempestphp/tempest-framework/pull/1435)).
- The `ViewComponent` interface and `{html}<x-component name="">` have been removed ([#1439](https://github.com/tempestphp/tempest-framework/pull/1439)). You must now always use file-based view components.

## What's next?

From the beginning I've said that IDE support is a must for any project to succeed. It now looks like there's a real chance of that happening, which is amazing. Besides IDE support, there are a couple of big features to tackle: I want Tempest to ship with some form of "standard component library" that people can use as a scaffold, we're looking into adding HTMX support (or something alike) to build async components, and we plan on making bridges for Laravel and Symfony so that you can use Tempest View in projects outside of Tempest as well.

If you're inspired and interested to help out with any of these features, then you're more than welcome to [join the Tempest Discord](/discord) and take it from there.

---

<!-- source: src/Web/Blog/articles/2025-07-29-tempest-1-5.md -->

---

title: Tempest 1.5
description: This release brings a new markdown view component, CSRF support, installable view components, and more.
tag: release
author: brent

---

## Installable view components

We made some pretty significant changes to view component's discovery. These changes now make it possible to ship view components from the framework or via third-party packages and publish them when needed:

```console
./tempest install view-components

 <dim>│</dim> <em>Select which view components you want to install</em>
 <dim>│</dim> / <dim>Filter...</dim>
 <dim>│</dim> → ⋅ x-csrf-token
 <dim>│</dim>   ⋅ x-markdown
 <dim>│</dim>   ⋅ x-input
 <dim>│</dim>   ⋅ x-icon

<comment>…</comment>
```

This refactor came with some breaking changes though. Tempest View is still an experimental component of the framework, so occasional breaking changes might happen. We documented the how and why of these changes in [a separate blog post](/blog/tempest-view-updates). In the end, these changes made a lot of sense, and it's great to see how [Discovery](/blog/discovery-explained) made the installer part with vendor- and project-based view components trivial to add.

Apart from the view component installer, we also made a bunch of fixes to how view components deal with local and global variable scope, and we added a bunch more built-in view components that ship with the framework:

- `{html}<x-base />`: a barebone base layout with Tailwind CDN included
- `{html}<x-form />`: a form component which posts by default and includes the csrf token out of the box
- `{html}<x-input />`: a flexible component to render form inputs
- `{html}<x-submit />`: renders a submit button
- `{html}<x-markdown />`: a component to render markdown, either inline or from a variable

You can read more about built-in view components in [the docs](/docs/essentials/views#built-in-components).

## CSRF support

Any form request will now have CSRF protection. Because CSRF protection is enabled by default, you will need to add the new `{html}<x-csrf-token />` element to your forms (it is included by default when you use `{html}<x-form />`).

```html
<form action="…">
  <x-csrf-token />
</form>
```

## Database pagination

The select query builder now has pagination support:

```php
$chapters = query(Chapter::class)
    ->select()
    ->whereField('book_id', $book->id)
    ->paginate();
```

## New `Json` response

We've added a new `Json` response class that can be returned from controllers and will include the necessary JSON headers:

```php
use Tempest\Http\Responses\Json;

#[Get('/books')]
public function books(): Response
{
    // …
    return new Json($books);
}
```

## View data testers

We added some additional assertion methods to our HTTP tester, so that you can make assertions on view data directly:

```php
public function test_can_assert_view_data(): void
{
    $this->http
        ->get(uri([TestController::class, 'withView']))
        ->assertViewData('name')
        ->assertViewData('name', function (array $data, string $value): void {
            $this->assertEquals(['name' => 'Brent'], $data);
            $this->assertEquals('Brent', $value);
        })
        ->assertViewDataMissing('email');
}
```

That's all the notable new features in Tempest 1.5. Of course, there are a bunch of bug fixes as well. Click here to read [the full changelog](https://github.com/tempestphp/tempest-framework/releases/tag/v1.5.0).

---

<!-- source: src/Web/Blog/articles/2025-09-16-tempest-2.md -->

---

title: Tempest 2.0
description: We've just tagged Tempest 2.0. It's a release focussed on fine-tuning and fixing lots of details. It also signifies that we're committed to Tempest, we're in this for the long run!
tag: release
author: brent

---

As we've said from the start: our aim is to make upgrades with Tempest as smooth as possible. Breaking changes are bound to happen in any project in this stage, and we want to burden our users as little as possible. That's why we added an easy, automated way which handles the upgrade to Tempest 2.0 for you. It should only take five minutes.

Tempest upgrades are handled via [Rector](https://getrector.com/). So before doing anything else, make sure Rector is installed in your project:

```
{:hl-comment:~:} composer require rector/rector --dev {:hl-comment:# to require rector as a dev dependency:}
{:hl-comment:~:} vendor/bin/rector {:hl-comment:# to create a default rector config file:}
```

Next, update Tempest; it's important to add the `--no-scripts` flag to prevent any errors from being thrown during the update.

```sh
{:hl-comment:~:} composer require tempest/framework:^2.0 --no-scripts
```

Then you should add the Tempest set to your Rector config file:

```php
// rector.php

use \Tempest\Upgrade\Set\TempestSetList;

return RectorConfig::configure()
    // …
    ->withSets([TempestSetList::TEMPEST_20]);
```

Then run the following commands

```
{:hl-comment:~:} vendor/bin/rector {:hl-comment:# To update all your project files:}
{:hl-comment:~:} ./tempest discovery:clear {:hl-comment:# Which is needed to make sure discovery cache is updated:}
{:hl-comment:~:} ./tempest key:generate {:hl-comment:# To generate a new signing key and should be done for every environment:}
{:hl-comment:~:} ./tempest migrate:rehash {:hl-comment:# To rehash all migrations, which internal workings were changed with this release:}
```

Finally, review and test your project and make sure to read through the list of the breaking changes below. The changes in **bold** are automated by Rector, the other ones are internal changes that should — _in theory_ — have no effect. Yet we wanted to mention them for transparency's sake.

- [#1458](https://github.com/tempestphp/tempest-framework/pull/1458): **`Tempest\Database\Id` is now called `Tempest\Database\PrimaryKey`**.
- [#1458](https://github.com/tempestphp/tempest-framework/pull/1458): **The value property of `Tempest\Database\PrimaryKey` has been renamed from `id` to `value`**.
- [#1507](https://github.com/tempestphp/tempest-framework/pull/1507): **`Tempest\CommandBus\AsyncCommand` is now called `Tempest\CommandBus\Async`**.
- [#1444](https://github.com/tempestphp/tempest-framework/pull/1444): **Validation rule names were updated**.
- [#1513](https://github.com/tempestphp/tempest-framework/pull/1513): **The `DatabaseMigration` interface was split into two**.
- **`\Tempest\uri` and `\Tempest\is_current_uri` are both moved to the `\Tempest\Router` namespace**.
- You cannot longer declare view components via the `{html}<x-component name="x-my-component">` tag. All files using this syntax must remove the wrapping `{html}<x-component` tag an[#1439](https://github.com/tempestphp/tempest-framework/pull/1439): d instead rename the filename to `x-my-component.view.php`. This was an undocumented feature and likely not used by anyone.
- [#1447](https://github.com/tempestphp/tempest-framework/pull/1447): Cookies are now encrypted by default and developers must run `tempest key:generate` once per environment.
- [#1435](https://github.com/tempestphp/tempest-framework/pull/1435): Changes in view component variable scoping rules might affect view files.
- [#1444](https://github.com/tempestphp/tempest-framework/pull/1444): The validator now requires the translator, and should always be injected instead of manually created.

Apart from these breaking changes, Tempest 2.0 also includes a range of bug fixes, internal refactors, and a handful of new features. You can [read the full release notes here](https://github.com/tempestphp/tempest-framework/releases/tag/v2.0.0).

## What's next?

There are [many more things to work on](https://github.com/tempestphp/tempest-framework/issues). My personal focus for now will be to get [FrankenPHP's worker mode support](https://github.com/tempestphp/tempest-framework/issues/1548) built-into Tempest. We're also working on a proper [PhpStorm plugin for Tempest View](https://github.com/tempestphp/tempest-phpstorm-plugin), and Enzo's focus will be on a debugging UI, as well as asynchronous transport features. Exciting times ahead!

Finally, if you're interested in trying Tempest out or in contributing, make sure to [join our Discord](/discord), where by now over 500 developers are gathered to work with and talk about Tempest.

## Troubleshooting

One issue you might run into during deployment are outdated discovery caches. You should be able to run `tempest discovery:clear`, but if for some reason that doesn't work, you can always manually remove your cache folder: `rm -r .tempest/cache/`.

If you happen to encounter such an issue, please let us know on [Discord](/discord) or via [GitHub](https://github.com/tempestphp/tempest-framework).

---

<!-- source: src/Web/Blog/articles/2025-09-19-migrations-in-tempest-2.md -->

---

title: No more down migrations
description: Database migrations have had a serious refactor in the newest Tempest release
author: brent
tag: thoughts

---

With Tempest 2 comes a pretty significant change to how database migrations work. Luckily, the [upgrade process is automated](/blog/tempest-2). I thought it would be interesting to explain _why_ we made this change, though.

Previously, the `DatabaseMigration` interface looked like this:

```php
interface DatabaseMigration
{
    public string $name { get; }

    public function up(): ?QueryStatement;

    public function down(): ?QueryStatement;
}
```

Each migration had to implement both an `up()` and `down()` method. If your migration didn't need `up()` or `down()` functionality, you'd have to return `null`. This design was originally inspired by Laravel, and was one of the very early parts of Tempest that had never really changed. However, Freek recently wrote [a good blog post](https://freek.dev/2900-why-i-dont-use-down-migrations) on why he doesn't write down migrations anymore:

> At Spatie, we've embraced forward-only migrations for many years now.
>
> When something needs to be reversed, we will first think carefully about the appropriate solution for the particular situation we’re in. If necessary, we’ll handcraft a new migration that moves us forward rather than trying to reverse history.

Freek makes the point that "trying to reverse history with down migrations" is pretty tricky, especially if the migrations you're trying to roll back are already in production. I have to agree with him: up-migrations can already be tricky; trying to have consistent down-migrations as well is a whole new level of tricky-ness.

After reading Freek's blog post, I remembered: Tempest is a clean slate. Nothing is stopping us from using a different approach. That's why we removed the `DatabaseMigration` interface in Tempest 2. Instead there are now both the {b`Tempest\Database\MigratesUp`} and {b`Tempest\Database\MigratesDown`} interfaces. Yes, we kept the `MigratesDown` interface for now, and I'll elaborate a bit more on why later. First, let me show you what migrations now look like:

```php
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateStoredEventTable implements MigratesUp
{
    public string $name = '2025-01-01-create_stored_events_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(StoredEvent::class)
            ->primary()
            ->text('uuid')
            ->text('eventClass')
            ->text('payload')
            ->datetime('createdAt');
    }
}
```

This is our recommended way of writing migrations: to only implement the {b`Tempest\Database\MigratesUp`} interface. Thanks to this refactor, we don't have to worry about nullable return statements on the interfaces as well, which I'd say is a nice bonus. Of course, you can still implement both interfaces in the same class if you really want to:

```php
use Tempest\Database\MigratesUp;
use Tempest\Database\MigratesDown;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

final class CreateStoredEventTable implements MigratesUp, MigratedDown
{
    public string $name = '2025-01-01-stored_events_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('stored_events')
            ->primary()
            ->text('uuid')
            ->text('eventClass')
            ->text('payload')
            ->datetime('createdAt');
    }

    public function down(): QueryStatement
    {
        return new DropTableStatement('stored_events');
    }
}
```

So why did we keep the `MigratesDown` interface? Some developers told me they like to use down migrations during development where they partially roll back the database while working on a feature. Personally, I prefer to always start from a fresh database and use [database seeders](/2.x/essentials/database#multiple-seeders) to bring it to a specific state. This way you'll always end up with the same database across developer machines, and can develop in a much more consistent way. You could, for example, make a seeder per feature you're working on, and so rollback the database to the right state during testing much more consistently:

```
./tempest migrate:fresh --seeder="Tests\Tempest\Fixtures\MailingSeeder"
{:hl-comment:# Or:}
./tempest migrate:fresh --seeder="Tests\Tempest\Fixtures\InvoiceSeeder"
```

Either way, we decided to keep `MigrateDown` in for now, and see the community's reaction to this new approach. We might get rid of down migrations altogether in the future, or we might keep them. Our recommended approach won't change, though: don't try to reverse the past, focus on moving forward.

---

<!-- source: src/Web/Blog/articles/2025-10-02-oauth-in-tempest.md -->

---

title: OAuth in Tempest 2.2
description: Tempest 2.2 gets a new OAuth integration which makes authentication super simple
author: brent
tag: release

---

Authentication is a challenging problem to solve. It's not just about logging a user in and session management, it's also about allowing them to manage their profile, email confirmation and password reset flows, custom authentication forms, 2FA, and what not. Ever since the start of Tempest, we've tried a number of approaches to have a built-in authentication layer that ships with the framework, and every time the solution felt suboptimal.

There is one big shortcut when it comes to authentication, though: outsource it to others. In other words: OAuth. Everything account-related can be managed by providers like Google, Meta, Apple, Discord, Slack, Microsoft, etc. All the while the implementation on our side stays incredibly simple. With the newest Tempest 2.2 release, we've added a firm foundation for OAuth support, backed by the incredible work done by the [PHP League](https://oauth2-client.thephpleague.com/). Here's how it works.

Tempest comes with support for many OAuth providers (thanks to the PHP League, again):

- [**GitHub**](https://github.com/tempestphp/tempest-framework/blob/main/packages/auth/src/OAuth/Config/GitHubOAuthConfig.php)
- [**Google**](https://github.com/tempestphp/tempest-framework/blob/main/packages/auth/src/OAuth/Config/GoogleOAuthConfig.php)
- [**Facebook**](https://github.com/tempestphp/tempest-framework/blob/main/packages/auth/src/OAuth/Config/FacebookOAuthConfig.php)
- [**Discord**](https://github.com/tempestphp/tempest-framework/blob/main/packages/auth/src/OAuth/Config/DiscordOAuthConfig.php)
- [**Instagram**](https://github.com/tempestphp/tempest-framework/blob/main/packages/auth/src/OAuth/Config/InstagramOAuthConfig.php)
- [**LinkedIn**](https://github.com/tempestphp/tempest-framework/blob/main/packages/auth/src/OAuth/Config/LinkedInOAuthConfig.php)
- [**Microsoft**](https://github.com/tempestphp/tempest-framework/blob/main/packages/auth/src/OAuth/Config/MicrosoftOAuthConfig.php)
- [**Slack**](https://github.com/tempestphp/tempest-framework/blob/main/packages/auth/src/OAuth/Config/SlackOAuthConfig.php)
- [**Apple**](https://github.com/tempestphp/tempest-framework/blob/main/packages/auth/src/OAuth/Config/AppleOAuthConfig.php)
- Any other OAuth platform by using {b`Tempest\Auth\OAuth\Config\GenericOAuthConfig`}.

Whatever OAuth providers you want to support, it's as easy as making a config file for them like so:

```php app/Auth/github.config.php
use Tempest\Auth\OAuth\Config\GitHubOAuthConfig;

return new GitHubOAuthConfig(
    tag: 'github',
    clientId: env('GITHUB_CLIENT_ID'),
    clientSecret: env('GITHUB_CLIENT_SECRET'),
    redirectTo: [GitHubAuthController::class, 'handleCallback'],
    scopes: ['user:email'],
);
```

```php app/Auth/discord.config.php
use Tempest\Auth\OAuth\Config\DiscordOAuthConfig;

return new DiscordOAuthConfig(
    tag: 'discord',
    clientId: env('DISCORD_CLIENT_ID'),
    clientSecret: env('DISCORD_CLIENT_SECRET'),
    redirectTo: [DiscordAuthController::class, 'callback'],
);
```

Now we're ready to go. Generating a login link can be done by using the {b`Tempest\Auth\OAuth\OAuthClient`} interface:

```php
namespace App\Auth;

use Tempest\Auth\OAuth\OAuthClient;
use Tempest\Container\Tag;
use Tempest\Router\Get;

final readonly class DiscordAuthController
{
    public function __construct(
        #[Tag('discord')]
        private OAuthClient $oauth,
    ) {}

    #[Get('/auth/discord')]
    public function redirect(): Redirect
    {
        return $this->oauth->createRedirect();
    }

    // …
}
```

Note how we're using [tagged singletons](/2.x/essentials/container#tagged-singletons) to inject our {b`Tempest\Auth\OAuth\OAuthClient`} instance. These tags come from the provider-specific configurations, and you can have as many different OAuth clients as you'd like. Finally, after a user was redirected and has authenticated with the OAuth provider, they will end up in the callback action, where we can authenticate the user on our side:

```php
namespace App\Auth;

use Tempest\Auth\Authentication\Authenticatable;
use Tempest\Auth\OAuth\OAuthClient;
use Tempest\Auth\OAuth\OAuthUser;
use Tempest\Container\Tag;
use Tempest\Router\Get;

final readonly class DiscordAuthController
{
    public function __construct(
        #[Tag('discord')]
        private OAuthClient $oauth,
    ) {}

    #[Get('/auth/discord')]
    public function redirect(): Redirect
    {
        return $this->oauth->createRedirect();
    }

    #[Get('/auth/discord/callback')]
    public function callback(Request $request): Redirect
    {
        $this->oauth->authenticate(
            $request,
            function (OAuthUser $user): Authenticatable {
                return query(User::class)->updateOrCreate([
                    'email' => $user->email,
                ], [
                    'discord_id' => $user->id,
                    'username' => $user->nickname,
                ]);
            }
        )

        return new Redirect('/');
    }
}
```

As you can see, there's still a little bit of manual work involved within the OAuth callback action. That's because Tempest doesn't make any assumptions on how "users" are modeled within your project and thus you'll have to create or store those user credentials somewhere yourself. However, we also acknowledge that some kind of "default flow" would be useful for projects that just need a simple OAuth login with a range of providers. That's why we're now working on adding an OAuth installer: it will prompt you which providers to add in your project, prepare all config objects and controllers for you, and will assume you're using our built-in [user integration](/2.x/features/authentication).

All in all, I think this is a very solid base to build upon. You can read more about using Tempest's OAuth integration in the [docs](/2.x/features/oauth), and make sure to [join our Discord](/discord) if you want to stay in touch!

---

<!-- source: src/Web/Blog/articles/2025-10-27-re-the-journey-thus-far.md -->

---

title: "RE: the journey this far"
description: Replying to someone trying out Tempest
author: brent
tag: thoughts

---

I recently stumbled upon a blogpost by Vyygir describing their first steps with Tempest, and I loved reading it. There were some good things, some bad things, and it's this kind of real-life feedback that is invaluable for Tempest to grow. I hope more people will do it in the future. Reading through it, I had some thoughts that I think might be a valuable addition, so I figured I'd do a "reply-style" blog post. You can read the [original one here](https://starle.sh/tempest-the-journey-thus-far), but I'll quote the parts I'm replying to over here as well.

> Let's start positively, purely so I can demonstrate that I'm not here to shit on someone's hard work.

Thank you! Appreciate it. What's especially good is that some of the design goals we set out from the very start are acknowledged by so many people who try out Tempest. It's great validation that there is indeed a need for it.

> There. I've done the positive bits. Now I can <s>be negative</s> provide my thoughts on my own experiences without feeling bad.

Don't feel bad, it's nice to hear good things, but even better what can be improved!

## The Structure

> I know this doesn't sound very open-minded but the build-your-whatever mindset that exists with Tempest, I feel, presents the same problem that I currently have with React: if you don't know how to actually build software that can scale well, then you're going to build something painfully unmaintainable that you'll hate in a few months. […] Shipping with some expected structures, even if it's a templated setup option, feels as though it'd offer more guidance and denote a structure from the offset, with expectancy.

I actually agree with Vyygir. Starting from a completely empty src directory can feel disorienting. It's actually on our roadmap to have two or three scaffold projects, which you can choose from based on your preference. We haven't gotten to that stage yet because, honestly, we're still trying to figure it out ourselves. Maybe we should stop using that excuse and just build _something_. [Noted](https://github.com/tempestphp/tempest-framework/issues/1665).

That being said, I've experimented a lot, and I've refactored a lot. The one thing that sets Tempest apart from other frameworks is that it truly _does not care_ about how your project is structured, and thus also doesn't care about refactorings. You can move everything around, and everything will keep working (given that you clear discovery caches in production). So even if you run into issues down the line, refactoring your project shouldn't be hard.

## Discovery

Moving on to Vyygir's thoughts about discovery:

> Let me start with this: I love the idea of Discovery. Composer takes us part-way there but Tempest's Discovery implementation absolutely nailed the execution.

Thank you! <small>Bracing for impact</small>

> That being said... I definitely missed the scope of what Discovery can do.

Ah, yes. This highlights a crucial drawback in our documentation. I did write a blog post about discovery to [explain it more in depth](/blog/discovery-explained), but it's rather hidden. Our docs currently assume too much that people already understand the concept of discovery, and this might be confusing to newcomers (Vyygir definitely isn't the only one). Also, [noted](https://github.com/tempestphp/tempest-framework/issues/1666).

However, there was one critique about discovery that I didn't fully understand:

> I had an idea that I'd use Discovery to find my entries in ./entries/\*.md and then load them into a repository. I even tried it. But the major problem I was hitting was that my EntryRepository wasn't actually in the container at the point of discovery which, when you read through the bootstrap steps actually makes a lot of sense.

The way Vyygir describes it should indeed work, and I'm curious to learn why it didn't. It's actually how discovery works at its core: it scans files (PHP files or any you'd like) and registers the result in some kind of dependency. Usually it's a singleton config, but it can be anything that is available in the container.

As a sidenote: Vyygir mentions that he let go of the idea after seeing the [source code of my blog](https://github.com/brendt/stitcher.io/blob/main/app/Blog/BlogPostRepository.php#L75) (where I do a runtime filescan on one directory instead of leveraging discovery). A good rule of thumb is to rely on discovery when file locations are unknown: discovery will be scanning your whole project and relevant vendor sources, and your specific discovery classes that interact with that scanning cycle. If you already know which folder will contain all relevant files (a content directory with markdown files, for example), then you're better off just directly interacting with that folder instead of relying on discovery.

Nevertheless, discovery should technically work for Vyygir's use case (up to you whether you want to use it or not). Maybe ha was running into an underlying issue, maybe something else was at play. Anyway, Vyygir, if you're reading this let me know, and I'm happy to help you debug.

## The Structure: Again but Different

> I had to make a last minute revision to the structure when I realised that DiscoveryLocation was not pleased with me trying to use a full cache strategy on views whilst having them outside of `src`.

Ok so, Vyygir wants their view files to live outside of `src`. While I personally disagree with this approach (IMO view files are an equally important part of a project's "source" as anything else), I also don't mind people who want to do it differently. That's the whole point of Tempest's flexibility: do it your way.

Vyygir ran into an issue: view files weren't discovered outside of `src`. This is, again, something [we should document](https://github.com/tempestphp/tempest-framework/issues/1667).

The solution is actually pretty simple: Tempest will discover any PSR-4 valid namespace. So if you want your view files to live outside of `src` or `app` or whatever, just add a namespace for it in composer.json:

```json
"autoload": {
    "psr-4": {
        "App\\": "src/",
        "Views\\": "views/"
    },
}
```

Your view files themselves don't need a namespace, mind you; this namespace is only here to tell Tempest that `views/` is a directory it should scan. Of course, if you happened to add a class in the `Views` namespace (like, for example, a [custom view object](/2.x/essentials/views#using-dedicated-view-objects)), then be my guest!

## What's wrong with abstractions?

> I get the usage of interfaces in the degree they are. But my god, sometimes, finding a reference is painful.
>
> I feel like nearly everything is pointing to a generic upper layer that only vaguely implies what might exist when you're trying to understand how a segment of functionality works to, you know, implement something. And, because of how new Tempest is, not everything is fully documented yet. And the public use cases are slim pickings.

I get it. The combination of interface + trait isn't the most ideal, and you might be tempted to ask "why not use an abstract class instead?" I have a philosophy on why I prefer interfaces over abstract classes, and I've written and spoken about it many times before:

- [https://stitcher.io/blog/extends-vs-implements](https://stitcher.io/blog/extends-vs-implements)
- [https://stitcher.io/blog/is-a-or-acts-as](https://stitcher.io/blog/is-a-or-acts-as)
- [https://www.youtube.com/watch?v=HK9W5A-Doxc](https://www.youtube.com/watch?v=HK9W5A-Doxc)

The tl;dr is that my view on inheritance is inspired by modern languages like Rust and Go, instead of following the "classic C++-style inheritance" we've become used to over the past decades.

PHP being PHP though, there are some drawbacks. More specifically that you need both the interface and trait, which introduces some complexity. That being said, I still believe that this approach is better than a classic inheritance tree, and I wish — oh how I wish — that PHP would solve it. Again, I've talked and written about this before, and even made a suggestion to internals:

- [https://www.youtube.com/watch?v=lXsbFXYwxWU](https://www.youtube.com/watch?v=lXsbFXYwxWU)
- [https://externals.io/message/125305#125305](https://externals.io/message/125305#125305)

Unfortunately, we haven't gotten a proper solution yet. My hope is that interface default methods will come back on the table, and the problem that Vyygir describes will be solved.

I would really encourage you to read up on the topic though, because as soon as it clicks, I find I almost never want to rely on abstract classes again, and my code becomes a lot more simple.

## View Syntax

> I'm going to be honest, I just struggle to parse this mentally in comparison to something like Twig. This is almost definitely a problem unique to me (because my brain don't do the working right). I just wanted to mention it though.

That's fair. That's why we have [built-in support for Twig and Blade](/2.x/essentials/views#using-other-engines) as well. We're actively working on a PhpStorm plugin for Tempest View, which will make life easier.

## `DateTime` (no, not that one)

> Oh. Tempest's DateTime uses... a whole other formatting structure that I'm totally unfamiliar with. Sigh. Do I want to spend the time to figure this out?

Ok so, story time. We wanted a DateTime library that was more powerful than PHP's built-in datetime, so that you could more easily work with date time objects. Stuff like adding or subtracting days, an easier interface to create datetime objects, … (you can read about it [here](https://tempestphp.com/2.x/features/datetime)).

There were two options: [Carbon](https://carbon.nesbot.com/docs/) or the [PSL](https://github.com/azjezz/psl) implementation. We went with the second one (and added a wrapper for it within the framework).

IMO, we've made a mistake. Here's what I dislike about:

- We have `Tempest\DateTime\DateTime`, which has a naming collision with `\DateTime`. I cannot count the number of times where I accidentally imported the wrong library
- Having used Carbon for years, it's really annoying getting used to another API, eg: `plusDay()` instead of `addDay()`, etc.
- The date format. Oh how I dislike the date format. Just to clarify, PSL's implementation relies on [the standardized ICU spec](https://unicode-org.github.io/icu/userguide/format_parse/datetime/#formatting-dates-and-times), which in fact is more widely used than PHP's "built-in" datetime formatting. For example, with Tempest's implementation you write `$dateTime->format('yyyy-MM-dd HH:mm:ss')` instead of `$dateTime->format('Y-m-d H:i:s')`. You could argue that this just requires some "getting used to", but I, for one, haven't gotten used to it, so I can imagine how frustrating it is for newcomers.

That being said, we should also note that using Tempest's implementation is totally opt-in. You can choose to use either PHP's built-in `\DateTime`, or `Carbon` instead. However, how to do so is also undocumented. Again, [noted](https://github.com/tempestphp/tempest-framework/issues/1668).

## In conclusion

I'm so thankful for Vyygir taking the time to write down their thoughts. I'm also happy that most of their pain points come down to improving the docs, more than anything else; and this feedback will make Tempest better. Thank you!

---

<!-- source: src/Web/Blog/articles/2025-11-10-route-decorators.md -->

---

title: "Route decorators in Tempest 2.8"
description: Taking a deep dive in a new Tempest feature
author: brent
tag: release

---

When I began working on Tempest, the very first features were a container and a router. I already had a clear vision on what I wanted routing to look like: to embrace attributes to keep routes and controller actions close together. Coming from Laravel, this is quite a different approach, and so I wrote about [my vision on the router's design](/blog/about-route-attributes) to make sure everyone understood.

> If you decide that route attributes aren't your thing then, well, Tempest won't be your thing. That's ok. I do hope that I was able to present a couple of good arguments in favor of route attributes; and that they might have challenged your opinion if you were absolutely against them.

One tricky part with the route attributes approach was route grouping. My proposed solution back in the day was to implent custom route attributes that grouped behavior together. For example, where Laravel would define "a route group for admin routes" like so:

```php
Route::middleware([AdminMiddleware::class])
    ->prefix('/admin')
    ->group(function () {
        Route::get('/books', [BookAdminController::class, 'index'])
        Route::get('/books/{book}/show', [BookAdminController::class, 'show'])
        Route::post('/books/new', [BookAdminController::class, 'new'])
        Route::post('/books/{book}/update', [BookAdminController::class, 'update'])
        Route::delete('/books/{book}/delete', [BookAdminController::class, 'delete'])
    });
```

Tempest's approach would look like this:

```php
use Attribute;
use Tempest\Http\Method;
use Tempest\Router\Route;
use function Tempest\Support\path;

#[Attribute]
final class AdminRoute implements Route
{
    public function __construct(
        public string $uri,
        public array $middleware = [],
        public Method $method = Method::GET,
    ) {
        $this->uri = path('/admin', $uri);
        $this->middleware = [AdminMiddleware::class, ...$middleware];
    }
}
```

```php
final class BookAdminController
{
    #[AdminRoute('/books')]
    public function index(): View { /* … */ }

    #[AdminRoute('/books/{book}/show')]
    public function show(Book $book): View { /* … */ }

    #[AdminRoute('/books/new', method: Method::POST)]
    public function new(): View { /* … */ }

    #[AdminRoute('/books/{book}/update', method: Method::POST)]
    public function update(): View { /* … */ }

    #[AdminRoute('/books/{book}/delete', method: Method::DELETE)]
    public function delete(): View { /* … */ }
}
```

While I really like attribute-based routing, grouping route behavior does feel… suboptimal because of attributes. A couple of nitpicks:

- Tempest's default route attributes are represented by HTTP verbs: `#[Get]`, `#[Post]`, etc. Making admin variants for each verb might be tedious, so in my previous example I decided to use one `#[AdminRoute]`, where the verb would be specified manually. There's nothing stopping me from adding `#[AdminGet]`, `#[AdminPost]`, etc; but it doesn't feel super clean.
- When you prefer to namespace admin-specific route attributes like `#[Admin\Get]`, and `#[Admin\Post]`, you end up with naming collisions between normal- and admin versions. I've always found those types of ambiguities to increase cognitive load while coding.
- This approach doesn't really scale: say there are two types of route groups that require a specific middleware (`AuthMiddleware`, for example), then you end up making two or more route attributes, duplicating that logic of adding `AuthMiddleware` to both.
- Say you want nested route groups: one for admin routes and then one for book routes (with a `/admin/books` prefix), you end up with yet another variant called `#[AdminBookRoute]` attribute, not ideal.

So… what's the solution? I first looked at Symfony, which also uses attributes for routing:

```php
#[Route('/admin/books', name: 'admin_books_')]
class BookAdminController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response { /* … */ }

    #[Route('/{book}/show')]
    public function show(Book $book): Response { /* … */ }

    #[Route('/new', methods: ['POST'])]
    public function new(): Response { /* … */ }

    #[Route('/{book}/update', methods: ['POST'])]
    public function update(): Response { /* … */ }

    #[Route('/{book}/delete', methods: ['DELETE'])]
    public function delete(): Response { /* … */ }
}
```

I think Symfony's approach gets us halfway there: it has the benefit of being able to define "shared route behavior" on the controller level, but not across controllers. You could create abstract controllers like `AdminController` and `AdminBookController`, which doesn't scale horizontally when you want to combine multiple route groups, because PHP doesn't have multi-inheritance. On top of that, I also like Tempest's design of using HTTP verbs to model route attributes like `#[Get]` and `#[Post]`, which is missing with Symfony. All of that to say, I like Symfony's approach, but I feel like there's room for improvement.

With the scene now being set, let's see the design we ended up with in Tempest.

## A Tempesty solution

A week ago, my production server suddenly died. After some debugging, I realized the problem had to do with the recent refactor of [my blog](https://stitcher.io) to Tempest. The RSS and meta-image routes apparently started a session, which eventually led to the server being overflooded with hundreds of RSS reader- and social media requests per minute, each of them starting a new session. The solution was to remove all session-related middleware (CSRF protection, and "back URL" support) from these routes. While trying to come up with a proper solution, I had a realization: instead of making a "stateless route" class, why not add an attribute that worked _alongside_ the existing route attributes? That's what led to a new `#[Stateless]` attribute:

```php
#[Stateless, {:hl-type:Get:}('/rss')]
public function rss(): Response {}
```

This felt like a really nice solution: I didn't have to make my own route attributes anymore, but could instead "decorate" them with additional functionality. The first iteration of the `#[Stateless]` attribute was rather hard-coded in Tempest's router (I was on the clock, trying to revive my server), it looked something like this:

```php
// Skip middleware that sets cookies or session values when the route is stateless
if (
    $matchedRoute->route->handler->hasAttribute(Stateless::class)
    && in_array(
        needle: $middlewareClass->getName(),
        haystack: [
            VerifyCsrfMiddleware::class,
            SetCurrentUrlMiddleware::class,
            SetCookieMiddleware::class,
        ],
        strict: true,
    )
) {
    return $callable($request);
}
```

I knew, however, that it would be trivial to make this into a reusable pattern. A couple of days later and that's exactly what I did: route decorators are Tempest's new way of modeling grouped route behavior, and I absolutely love them. Here's a quick overview.

First, route decorators work _alongside_ route attributes, not as a _replacement_. This means that they can be combined in any way you'd like, and they should all work together seeminglessly:

```php
final class BookAdminController
{
    #[{:hl-type:Admin:}, {:hl-type:Books:}, {:hl-type:Get:}('/{book}/show')]
    public function show(Book $book): View { /* … */ }

    // …
}
```

Furthermore, route decorators can also be defined on the controller level, which means they'll be applied to all its actions:

```php
#[{:hl-type:Admin:}, {:hl-type:Books:}]
final class BookAdminController
{
    #[Get('/')]
    public function index(): View { /* … */ }

    #[Get('/{book}/show')]
    public function show(Book $book): View { /* … */ }

    #[Post('/new')]
    public function new(): View { /* … */ }

    #[Post('/{book}/update')]
    public function update(): View { /* … */ }

    #[Delete('/{book}/delete')]
    public function delete(): View { /* … */ }
}
```

Finally, you're encouraged to make your custom route attributes as well (you might have already guessed that because of `#[Admin]` and `#[Books]`). Here's what both of these attributes would look like:

```php
use Attribute;
use Tempest\Router\RouteDecorator;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final readonly class Admin implements RouteDecorator
{
    public function decorate(Route $route): Route
    {
        $route->uri = path('/admin', $route->uri)->toString();
        $route->middleware[] = AdminMiddleware::class;

        return $route;
    }
}
```

```php
use Attribute;
use Tempest\Router\RouteDecorator;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final readonly class Books implements RouteDecorator
{
    public function decorate(Route $route): Route
    {
        $route->uri = path('/books', $route->uri)->toString();

        return $route;
    }
}
```

You can probably guess what a route decorator's job is: it is passed the current route, it can do some changes to it, and then return it. You can add and combine as many route decorators as you'd like, and Tempest's router will stitch them all together. Under the hood, that looks like this:

```php
// Get the route attribute
$route = $method->getAttributes(Route::class);

// Get all decorators from the method and its controller class
 $decorators = [
    ...$method->getDeclaringClass()->getAttributes(RouteDecorator::class),
    ...$method->getAttributes(RouteDecorator::class),
];

// Loop over each decorator and apply it one by one
foreach ($decorators as $decorator) {
    $route = $decorator->decorate($route);
}
```

As an added benefit: all of this route decorating is done during [Tempest's discovery phase](/2.x/internals/discovery), which means the decorated route will be cached, and decorators themselves won't be run in production.

On top of adding the {b`Tempest\Router\RouteDecorator`} interface, I've also added a couple of built-in route decorators that come with the framework:

- {b`Tempest\Router\Prefix`}: which adds a prefix to all decorated routes.
- {b`Tempest\Router\WithMiddleware`}: which adds one or more middleware classes to all decorated routes.
- {b`Tempest\Router\WithoutMiddleware`}: which explicitely removes one or more middleware classes from the default middleware stack to all decorated routes.
- {b`Tempest\Router\Stateless`}: which will remove all session and cookie related middleware from the decorated routes.

I really like the solution we ended up with. I think it combines the best of both worlds. Maybe you have some thoughts about it as well? [Join the Tempest Discord](/discord) to let us know! You can also read all the details of route decorators [in the docs](/2.x/essentials/routing#route-decorators-route-groups).

---

<!-- source: src/Web/Blog/articles/2026-01-13-open-source-strategies.md -->

---

title: "Open source strategies"
description: Staying happy and productive while doing open source
author: brent
tag: thoughts

---

Imagine getting a group of 20 to 50 random people together in a room, all having to work on the same project. They have different backgrounds, educations, timezones, cultures — and your job is to guide them to success. Does that sound challenging enough? Let's say these people come and go whenever they please, sometimes finishing a task, sometimes doing it half, sometimes having AI do it for them without any review, and some people are simply there to angrily shout from the sideline.

Writing it like that, it's crazy to think that any open source project can be successful.

However, many projects are, and I've got to experience that first hand, being involved in open source for over a decade. First were some hobby projects, then I worked at [Spatie](https://spatie.be/open-source) where I helped build and maintain around 200 Laravel and PHP packages, and in recent years there's [Tempest](https://github.com/tempestphp/tempest-framework). What's interesting is that, even though I know fairly well how to code, "open source" was a whole new skill I had to learn; one I've come to like as much as writing actual code (or maybe even more).

At its core, **open source is a "people problem", more than a technical one**; and for me, solving that problem is exactly what makes open source so much fun.

Over the years, I had to learn several ways of navigating and dealing with that "people problem". Some things I learned from colleagues, some from other open source maintainers, some lessons I had to learn on my own. In this post, I want to bundle these findings for myself to remember and maybe for others to learn.

## Putting my ego aside

In the past, I've definitely worked on open source projects chasing my own fame and fortune. However, looking at [Tempest's contribution stats](https://github.com/tempestphp/tempest-framework/graphs/contributors), I can only conclude that there is no such thing as _my_ open source project. It was only able to get where it is now because of the efforts, contribution, and collaboration of many people — oftentimes more skilled and talented than me.

I realized that by empowering others, the project benefits. This sometimes means putting _my_ needs aside and truly listening to the needs of others. That isn't always an easy thing to do, but it has a very powerful consequence: when contributors feel appreciated and acknowledged, they often want to be involved even more. Eventually they themselves become advocates for the project, leading to even more people getting involved, and the process repeats.

Helping others to thrive is a core principle in successful collaborative open source.

## BDFL

It might seem contradictory to my first point, but I'm a firm believer of _one person having the final say_ — a [<u>B</u>enevolent <u>D</u>ictator <u>F</u>or <u>L</u>ife](https://en.wikipedia.org/wiki/Benevolent_dictator_for_life). That's what many popular open source projects have called it in the past.

Where people come together, there will inevitably be differences in opinions. Some opinions might be objectively _bad_, but frequently there are _gray_ areas without one objectively _right_ answer. When these situations arise, a successful open source project needs _one person_ to make the final decision. This _dictator_ should, of course, take all arguments into account. Likely they will surround themselves with a close group of confidants, but in the end, it's their decision and theirs alone. They guard the vision of the project, they make sure it stays on track.

## Say no

Sometimes an idea isn't bad at all, but still I have to say "no".

Because of the "open" nature of open source, people come and go. They contribute to the codebase free of charge, but they are equally not obliged to maintain their code either. In the end, it's me having the final responsibility over this project, and so sometimes I say "no" because I don't feel capable or comfortable maintaining whatever is being proposed in the long run.

## Say thanks

Whether I merge or not; whether a PR is the biggest pile of crap I've ever seen or not; I make a point of always saying thanks. Think about it: people have set apart time to contribute to this project. The least I can do is to write a genuine "thank you" note.

For the same reason, I try to be quick in responding to new issues and PRs — I don't always succeed, but I try. This lets people know their effort is seen — even though it might eventually not end up being merged. I try to value the intent over the result, which again, circles back to making others thrive.

## Opinion driven

I prefer code to be opinionated. Trying to solve all problems and edge cases is a fallacy, especially within open source where there will always be someone coming up with a use case no one else in the world has thought of. The reality is that time and resources are limited, which means that adding all knobs and pulls and configuration to please everyone is impossible.

Years of practice have shown that this strategy works. While people are often taken aback by it at first, it turns out to not be the blocker they feared it would.

## Automate the boring parts

Besides the people side of open source, my passion is still with code. With Tempest, I'm lucky to have a friend who's very skilled with the devops side and has helped set up a robust CI pipeline. I probably wouldn't have been able to do that myself without help (and many frustrations), but I simply cannot live without it anymore: from code style reviews to static analysis, from testing to subsplitting packages; everything is automated, and it saves so much time.

## Keep moving forward

I tag often — usually whenever there's something to tag — I'm not limited to a fixed release cycle. This means that people's contributions become publicly available very quickly, which contributors seem to appreciate.

One thing to take into account with having so many new releases (sometimes several per week, sometimes even several per day), is that you have to disconnect "releases" and "marketing" from each other. Where many open source projects think of "a new major release" as a once-every-one-or-two-years event that has to generate lots of buzz, I find that disconnecting the two makes life a lot more easy. I write feature highlight blog posts whenever there's time to do so, and simply mention "this feature is available since version X".

Another positive consequence is that you can easily spread out public communication about your project across time, which tends to have a strong long-term effect than communicating "everything that's new" in a single blog post or video.

## Take breaks

Finally: the realization that the world won't end when people take a break. I just had a three-week break where I totally disconnected. It seriously helped me to reenergize and sharpen my focus again. I want to encourage regular contributors to my projects to do the same. Take a break, you're winning in the long run.

---

For now, those are the things I wanted to write down. If anything, I'll use this list as a personal reminder from time to time to keep my priorities straight. And maybe it'll help others as well.

---

<!-- source: src/Web/Blog/articles/2026-02-12-tempest-3.md -->

---

title: Tempest 3.0
description: Tempest 3.0 comes with a new exception handler, several performance improvements, PHP 8.5 support, and more.
tag: release
author: brent

---

Tempest 3.0 is now available, and I want to take a moment to specifically thank all contributors who helped with this release. We've seen a continuous growth in the Tempest community over these past two years, and it's amazing to work with so many talented developers. So thank you all!

Later in this post, I'll list [all breaking changes and how to use the automatic upgrader for existing projects](#breaking-changes-and-automatic-upgrades). First, I want to highlight some of the awesome new features in Tempest 3.0, you can also [read the full changelog here](https://github.com/tempestphp/tempest-framework/releases/tag/v3.0.0).

## New exception handler

Since the very start of Tempest, we relied on Whoops to render our error pages. While it worked, we always envisioned a more modern exception render that was easier to finetune to our needs. With Tempest 3.0 we took the first steps in making this vision a reality.

![](/img/tempest-3-exception.png)

Props to Enzo for taking the lead on this one. In the future, we want to continue to improve this page, and also further build on it to make debugging Tempest apps even better.

## PHP 8.5

I wrote about my vision for only supporting the latest PHP version over a year ago [on my personal blog](https://stitcher.io/blog/php-84-at-least), and this year we're continuing that same trend: Tempest 3.0 only supports PHP 8.5 or higher. The reasons are outlined in detail in that blog post, but the most prominent reasons are these:

- Delaying upgrades only postpones and complicates the work, it never solves any problems.
- I believe in OSS maintainers having a responsibility to push the PHP community forwards.
- We want Tempest to continue to be a modern framework. We can only do that by evolving together with PHP.

## CSRF protection changes

We moved away from a classic CRSF-token approach to using [`{txt}Sec-Fetch-Site`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Sec-Fetch-Site) and [`{txt}Sec-Fetch-Mode`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Sec-Fetch-Mode). This means that the `{html}<x-csrf />` token has been removed and you don't need it anymore.

You can read about the behind-the-scenes in [the pull request](https://github.com/tempestphp/tempest-framework/pull/1829).

## Database improvements

We've done several improvements in the ORM and database components: we worked on [performance updates](https://github.com/tempestphp/tempest-framework/pull/1855) that make our ORM significantly faster; we also [support UUIDs as primary columns](https://github.com/tempestphp/tempest-framework/pull/1807); and we improved [`Query::toRawSql()`](https://github.com/tempestphp/tempest-framework/pull/1861) to make debugging complex queries a lot easier.

## Closure-based validation

Thanks to PHP 8.5, we can now support closure-based validation:

```php
use Tempest\Database\IsDatabaseModel;
use Tempest\Validation\Rules\ValidateWith;

final class Book
{
    use IsDatabaseModel;

    #[ValidateWith(static function (string $value): bool {
        return ! str_starts_with($value, ' ');
    })]
    public string $title;
}
```

Special thanks to [Mohammad](https://github.com/tempestphp/tempest-framework/pull/1828) for adding this!

## View improvements

We improved our view parser so that [whitespaces are kept as-is](https://github.com/tempestphp/tempest-framework/pull/1881). This makes it easier to debug compiled views, and also fixes some edge cases where white-spaces were wrongly stripped away. On top of that, we continued to improve Tempest View's performance, and added support for fallthrough attributes (special thanks to [Márk](https://github.com/tempestphp/tempest-framework/pull/1811) for that one)!

```html
<!-- x-test.view.php -->
<div class="test">
  <x-slot />
</div>

<!-- home.view.php -->
<x-test :class="$shouldHighlight ? 'bg-red-100' : ''"> … </x-test>

<!-- These attributes will now be merged correctly: -->
<!-- <div class="test bg-red-100"> -->
```

## OAuth improvements

Thanks to [iamdadmin](https://github.com/tempestphp/tempest-framework/pull/1919), our [OAuth support](../3.x/features/oauth) now also includes Twitch.

```php oauth-twitch.config.php
use Tempest\Auth\OAuth\Config\TwitchOAuthConfig;

return new TwitchOAuthConfig(
    clientId: env('TWITCH_CLIENT_ID'),
    clientSecret: env('TWITCH_CLIENT_SECRET'),
    redirectTo: [TwitchOAuthController::class, 'callback'],
);
```

We also fixed an annoying bug so that you can [automatically run migrations after installing one or more OAuth providers](https://github.com/tempestphp/tempest-framework/pull/1927).

## Console

Márk also added [support for console autocompletion](https://github.com/tempestphp/tempest-framework/pull/1851) in zsh and bash. It's as easy as running the `tempest completion:install` command, and you can [read more about it here](/3.x/essentials/console-commands#shell-completion).

Console autocompletion tends to be a tricky one to get right for all systems, so if you run into issues, please [let us know](https://github.com/tempestphp/tempest-framework).

## Breaking changes and automatic upgrades

Since Tempest is still a young framework, breaking changes are to be expected as we polish our codebase. As with the previous major release, we shipped an automatic upgrader, powered by [Rector](https://getrector.com/). First, make sure to install Rector in your project if you haven't already:

```
{:hl-comment:~:} composer require rector/rector --dev {:hl-comment:# to require rector as a dev dependency:}
{:hl-comment:~:} vendor/bin/rector {:hl-comment:# to create a default rector config file:}
```

Next, update Tempest; it's important to add the `--no-scripts` flag to prevent any errors from being thrown during the update.

```sh
{:hl-comment:~:} composer require tempest/framework:^3.0 --no-scripts
```

Then configure Rector to upgrade to Tempest 3.0:

```php
// rector.php

use \Tempest\Upgrade\Set\TempestSetList;

return RectorConfig::configure()
    // …
    ->withSets([TempestSetList::TEMPEST_30]);
```

Finally, run Rector:

```
{:hl-comment:~:} vendor/bin/rector {:hl-comment:# To update all your project files:}
```

Unfortunately we weren't able to automate the full upgrade because we're running into some limitations with Rector. In the future, we want to look into alternatives to truly automate the whole upgrade. If you have very extensive Rector knowledge and want to help out, feel free to get in touch via our [Discord server](/discord) or [GitHub](https://github.com/tempestphp/tempest-framework).

To make sure you don't miss anything, here's a list of all breaking changes with links to their pull requests:

- [Deprecated testing utilities were removed](https://github.com/tempestphp/tempest-framework/pull/1849)
- [View and route testing helpers were moved to their correct classes](https://github.com/tempestphp/tempest-framework/pull/1870)
- [Exception handling has been reworked](https://github.com/tempestphp/tempest-framework/pull/1819)
- [Session management and CSRF protection has been reworked](https://github.com/tempestphp/tempest-framework/pull/1829)
- [The `view` function has been moved to the `Tempest\View` namespace](https://github.com/tempestphp/tempest-framework/pull/1860)
- [`Arr\map_iterable` has been renamed to `Arr\map`](https://github.com/tempestphp/tempest-framework/pull/1884)
- [`--force` can now bypass `CautionMiddleware`](https://github.com/tempestphp/tempest-framework/pull/1804)
- [`Environment` was made an injectable dependency](https://github.com/tempestphp/tempest-framework/pull/1838)
- [Enum events are now supported in the event bus](https://github.com/tempestphp/tempest-framework/pull/1878)
- [Several other core functions have been moved to the correct namespace](https://github.com/tempestphp/tempest-framework/pull/1880)
- Both [LogConfig](#) and [DatabaseConfig](#) have been refactored and must be manually updated.

## What's next?

We've already started work on a new list of features and fixes for the [3.x release cycle](https://github.com/tempestphp/tempest-framework/milestone/20). Some big items coming up are: a dedicated debugging AI, FrankenPHP worker mode support, and a complete overhaul of our event and command bus to make them seriously more powerful. Stay tuned.

---

<!-- source: src/Web/Blog/articles/2026-02-16-generating-typescript-types-with-tempest.md -->

---

title: Generating TypeScript types with Tempest
description: Tempest now has the ability to generate TypeScript interfaces from PHP classes to ease integration with TypeScript-based front-ends.
tag: release
author: brent

---

Tempest 3.1.0 was just released, and with it comes a new `generate:typescript-types` command. This command will take any value objects, DTOs, or enums written in PHP and generate TypeScript equivalents for them that you can use in your frontend. The only thing you need is annotated PHP code with [`#[AsType]`](https://github.com/tempestphp/tempest-framework/blob/3.x/packages/generation/src/TypeScript/AsType.php), and Tempest handles the rest.

Let's say you have this class:

```php
namespace App\Web\Blog;

use Tempest\Generation\TypeScript\AsType;
// …

#[AsType]
final class BlogPost
{
    public string $slug;
    public string $title;
    public ?Author $author;
    public string $content;
    public DateTimeImmutable $createdAt;
    public ?BlogPostTag $tag = null;
    public ?string $description = null;
    public bool $published = true;
    public array $meta = [];

    public string $uri {
        get => uri([BlogController::class, 'show'], slug: $this->slug);
    }

    public string $metaImageUri {
        get => uri([MetaImageController::class, 'blog'], slug: $this->slug);
    }
}
```

Next, you run:

```console
./tempest generate:typescript-types

<success>✓ // Generated 3 type definitions across 1 namespaces.</success>
```

Which will generate:

```js
/*
|----------------------------------------------------------------
| This file contains TypeScript definitions generated by Tempest.
|----------------------------------------------------------------
*/

export {:hl-keyword:namespace:} {:hl-type:App.Web.Blog:} {
    export {:hl-keyword:type:} {:hl-type:Author:} = 'brent';
    export {:hl-keyword:type:} {:hl-type:BlogPostTag:} = 'release' | 'thoughts' | 'tutorial';
    export interface {:hl-type:BlogPost:} {
        slug: {:hl-type:string:};
        title: {:hl-type:string:};
        {:hl-property:author?:}: {:hl-type:Author:};
        content: {:hl-type:string:};
        createdAt: {:hl-type:string:};
        {:hl-property:tag?:}: {:hl-type:BlogPostTag:};
        {:hl-property:description?:}: {:hl-type:string:};
        published: {:hl-type:boolean:};
        meta: {:hl-type:any[]:};
        uri: {:hl-type:string:};
        metaImageUri: {:hl-type:string:};
    }
}
```

Of course, Tempest will [discover](/3.x/essentials/discovery) all relevant classes for you, you can optionally configure how TypeScript files are generated, and you can even add your own type resolvers where needed. You can read all about it in [the TypeScript docs](/3.x/features/typescript). A massive thanks to Enzo for building this awesome feature!

---

<!-- source: src/Web/Blog/articles/2026-02-20-view-source-mapping.md -->

---

title: Tempest View with source mapping
description: Tempest 3.2 improves View debugging by introducing source maps.
tag: release
author: brent

---

With Tempest 3.2, we've made a significant improvement for debugging view files. For context: Tempest Views are compiled to normal PHP files, and if you were to encounter a runtime error in those compiled files (unknown variables, missing imports, etc.) — in those cases the stack trace used to look something like this:

![](/img/view-source-mapping-before.png)

As you can see, there's little useful information here: it points to the compiled file, the line numbers are messed up as well, and in general you wouldn't know the source of the problem. If you wanted to debug this error, you'd have to open the compiled view and read through a lot of compiled (and frankly, ugly) code. Ever since we switched to our own view parser though, we wanted to fix this issue. Even when a runtime error occurred in a compiled view, we want the stack trace to point to the source file.

And that's exactly what we did: we now keep track of the source file and line numbers while parsing Tempest View files, and from that data, we can resolve the correct stack trace when an error occurs:

![](/img/view-source-mapping-after.png)

This was a crucial feature to make Tempest View truly developer-friendly. Special thanks to [Márk](https://github.com/tempestphp/tempest-framework/pull/1980) for implementing it!

---

<!-- source: src/Web/Blog/articles/2026-03-13-truly-decoupled-discovery.md -->

---

title: Truly decoupled discovery
description: Tempest's discovery can now be used in any project
tag: release
author: brent

---

Making the Tempest components work in all types of projects has been a goal from the very start of the framework. For example, [`tempest/view`](/3.x/essentials/views#tempest-view-as-a-standalone-engine) can already be plugged into any project or framework you'd like.

Today we're making another component truly standalone: [`tempest/discovery`](/3.x/essentials/discovery). Discovery is what powers Tempest: it reads all your project and vendor code and configures that code in a PSR-11 compliant container for you. It's a simple idea, but really powerful when put into practice. And while frameworks like Symfony and Laravel have similar capabilities for framework-specific classes, Tempest's discovery is built to be extensible for all code.

In this blog post, I'll show you how to use `tempest/discovery` in any project, with any type of container, and I'll explain the impact for existing Tempest applications.

## Using discovery

You start by requiring `tempest/discovery` in any project, it could be a framework like Symfony or Laravel, a vanilla PHP app, anything.

```console
composer require tempest/discovery
```

The next step is to have a PSR-11 container. You can think of discovery as an extension for containers. In this case we can use the `php-di` container. If you're working within another framework like Laravel or Symfony, their containers already implement PSR-11 and you can use them directly.

```console
composer require php-di/php-di
```

The next step is to boot discovery. This means discovery will scan all your project and vendor files and pass them to discovery classes to be processed.

```php ./index.php
use Tempest\Discovery\BootDiscovery;
use Tempest\Discovery\DiscoveryConfig;
use DI\Container;

// Usually this container is already provided by whatever framework you're using
$container = new Container();

new BootDiscovery(
    container: $container,
    config: DiscoveryConfig::autoload(__DIR__),
)();
```

As a shorthand, `DiscoveryConfig::autoload(__DIR__)` will check the provided path for a `composer.json` file, and find scannable locations based on that. You can, of course, manually provide locations to scan as well:

```php
use Tempest\Discovery\DiscoveryConfig;
use Tempest\Discovery\DiscoveryLocation;
// …

$config = new DiscoveryConfig(locations: [
    new DiscoveryLocation('App\\', 'app/'),
]);

new BootDiscovery(
    container: $container,
    config: $config,
)();
```

That's all for the basic setup. If you want more complex configuration and learn about caching, head over to [the discovery docs](/3.x/essentials/discovery#discovery-as-a-standalone-package). Now that we've set discovery up, though, what exactly can you do with it?

### An example

Let's say you're building an event-sourced system where "projectors" can be used to replay all previously stored events. You want to build a command that shows all available projectors where the user can select the relevant projectors. Furthermore, whenever an event is dispatched, you need to loop over that same list of projectors to find out which events should be passed to which ones.

The interface would look something like this:

```php
interface Projector
{
    public function dispatch(object $event): void;

    public function clear(): void;
}
```

And a (simplified) implementation could look like this:

```php
final class VisitsPerDayProjector implements Projector
{
    public function onPageVisited(PageVisited $pageVisited): void
    {
        // Perform the necessary queries for this projector.
    }

    public function dispatch(object $event): void
    {
        if ($event instanceof PageVisited) {
            $this->onPageVisited($event);
        }
    }

    public function clear(): void
    {
        // Clear the projector to be rebuilt from scratch
    }
}
```

In other words: we need a list of classes that implement the `Projector` interface. This is where discovery comes in. A discovery class implements the {b`Tempest\Discovery\Discovery`} interface, which themselves are discovered as well. No need to register them anywhere; discovery takes care of it for you.

```php src/Discovery/ProjectorDiscovery.php
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class ProjectorDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly ProjectorConfig $config,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->implements(Projector::class)) {
            $this->discoveryItems->add($location, $class);
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $class) {
            $this->config->projectors[] = $class->getName();
        }
    }
}
```

This discovery class will take care of registering all projectors in whatever directories you specified at the start. It will store them in an object `ProjectorConfig`, which we assume is registered as a singleton in the container — meaning it's accessible throughout the rest of your codebase, and you can inject it anywhere you want. For example, in that console command:

```php
final readonly class EventsReplayCommand
{
    use HasConsole;

    public function __construct(
        private ProjectorConfig $projectorConfig,
    ) {}

    #[ConsoleCommand]
    public function __invoke(?string $replay = null): void
    {
        foreach ($this->projectorConfig->projectors as $projectorClass) {
            // …
        }
    }
}
```

In an event bus middleware:

```php
final readonly class StoredEventMiddleware implements EventBusMiddleware
{
    public function __construct(
        private ProjectorConfig $projectorConfig,
    ) {}

    #[Override]
    public function __invoke(string|object $event, EventBusMiddlewareCallable $next): void
    {
        // …

        foreach ($this->projectorConfig->projectors as $projectorClass) {
            // Dispatch the event to the relevant projectors
        }
    }
}
```

Or anywhere else. Zero config needed. That's the power of discovery.

### What else?

What else can you do with discovery? Basically anything you can imagine that you don't want to configure manually. In Tempest, we use it to discover routes, console commands, database migrations, objects marked for TypeScript generation, static pages, event listeners, command handlers, and a lot more.

The concept of discovery isn't new; other frameworks have proven that it's a super convenient way to write code. Tempest simply takes it to the next level and allows you to use it in any project you want — that's because Tempest truly gets out of your way 😁

## Impact on Tempest projects

We had to do a small refactor to make discovery truly standalone. In theory, you shouldn't be affected by these changes, unless your Tempest project was fiddling with some lower-level framework components. Luckily, you're not on your own. As with every Tempest upgrade, we make the process as easy as possible with Rector.

For starters, install Rector if you haven't yet:

```
composer require rector/rector --dev
vendor/bin/rector
```

Next, update Tempest; it's important to add the `--no-scripts` flag to prevent any errors from being thrown during the update.

```sh
composer require tempest/framework:^3.4 --no-scripts
```

Then configure Rector to upgrade to Tempest 3.4:

```php
// rector.php

use \Tempest\Upgrade\Set\TempestSetList;

return RectorConfig::configure()
    // …
    ->withSets([TempestSetList::TEMPEST_34]);
```

Next, run Rector:

```
vendor/bin/rector
```

Finally: clear config and discovery caches, and regenerate discovery:

```
rm -r .tempest/cache/config
rm -r .tempest/cache/discovery
./tempest discovery:generate
```

And that's it! Just in case you want to know all the details of this refactor, you can head over to [the pull request](https://github.com/tempestphp/tempest-framework/pull/2041) to see a list of changes that might affect you.

## In closing

The Tempest community has been using discovery for years, and without any exception, everyone simply loves how frictionless their development workflow has become because of it. Of course there's more to learn on how to configure discovery and setup caching, so head over to [the discovery docs](/3.x/essentials/discovery) to learn more.

Finally, come [join our Discord](/discord) if you're interested in Tempest or want to further talk about discovery. We'd love to hear from you!

---

<!-- source: src/Web/Blog/articles/2026-03-26-idempotency-in-tempest.md -->

---

title: Idempotency in Tempest
description: We've recently added an idempotency feature into Tempest to help you avoid code running twice when it shouldn't.
tag: release
author: brent

---

Oftentimes you need to ensure an operation only runs once: creating payments, generating invoices, provisioning resources, and what not; you want to prevent these things happening twice or more when they should only happen once. That's where our new idempotency package comes in. You can now mark routes and commands with the `#[Idempotent]` attribute to make sure they won't be run multiple times when they shouldn't.

Here's an example of a controller action:

```php
use Tempest\Idempotency\Attributes\Idempotent;
use Tempest\Router\Post;

final readonly class OrderController
{
    #[Idempotent]
    #[Post('/orders')]
    public function create(CreateOrderRequest $request): Response
    {
        $order = $this->orderService->create($request);

        return new GenericResponse(
            status: Status::CREATED,
            body: ['id' => $order->id],
        );
    }
}
```

Whenever this controller action is called, the `#[Idempotent]` attribute will make sure it only runs once within the context of an "[idempotency key](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Idempotency-Key)", and return a cached result for subsequent requests.

This "idempotency key", by the way, is a header the client sends; any request with the same idempotency key will be considered "the same".

```txt
POST /orders HTTP/1.1
Idempotency-Key: 550e8400-e29b-41d4-a716-446655440000
Content-Type: application/json

{"product": "widget", "quantity": 3}
```

Similar to idempotent routes, Tempest also supports idempotent commands. You can tag either a command or its handler with the same `#[Idempotent]` attribute:

```php
use Tempest\Idempotency\Attributes\Idempotent;
use Tempest\CommandBus\CommandHandler;

final class ImportInvoicesHandler
{
    #[Idempotent]
    #[CommandHandler]
    public function handleImportInvoices(ImportInvoicesCommand $command): void
    {}
}
```

By default, command idempotency is determined by the command's payload. However, commands can also implement the {b`Tempest\Idempotency\HasIdempotencyKey`} interface to provide a key which determines uniqueness (similar to the HTTP header for routes):

```php
use Tempest\Idempotency\Attributes\Idempotent;
use Tempest\Idempotency\HasIdempotencyKey;

#[Idempotent]
final readonly class ProcessPaymentCommand implements HasIdempotencyKey
{
    public function __construct(
        public string $paymentId,
        public int $amount,
    ) {}

    public function getIdempotencyKey(): string
    {
        return $this->paymentId;
    }
}
```

Finally, idempotency can be configured in many ways as well. You can [read all about it in the docs](/3.x/features/idempotency).

---

<!-- source: src/Web/Blog/articles/2026-03-27-new-orm-relations.md -->

---

title: New ORM relations
description: Tempest's ORM now supports HasOneThrough, HasManyThrough, and BelongsToMany relations
tag: release
author: brent

---

Thanks to the work of [Layla Tichi](https://github.com/tempestphp/tempest-framework/issues?q=sort%3Aupdated-desc+is%3Apr+author%3Alaylatichy), Tempest's ORM has gotten a significant upgrade.

First, there's the {b`#[Tempest\Database\HasOneThrough]`} attribute. It defines a one-to-one relationship that traverses through an intermediate model. This lets you access a distant relation directly, resolved in a single SQL query with two JOINs.

```php
use Tempest\Database\HasOne;
use Tempest\Database\HasOneThrough;

final class Author
{
    #[HasOne]
    public ?Profile $profile = null;

    #[HasOneThrough(Profile::class)]
    public ?Address $address = null;
}
```

Here's what the join statement looks like:

```sql
LEFT JOIN profiles ON profiles.author_id = authors.id
LEFT JOIN addresses ON addresses.profile_id = profiles.id
```

Next is the {b`#[Tempest\Database\HasManyThrough]`} attribute. This one defines a one-to-many relationship that traverses through an intermediate model. This lets you access a collection of distant relations directly, resolved in a single SQL query with two JOINs.

```php
use Tempest\Database\HasManyThrough;

final class Author
{
    /** @var \App\Payment\Payment[] */
    #[HasManyThrough(Contract::class)]
    public array $payments = [];
}
```

Here's what that join statement looks like:

```sql
LEFT JOIN contracts ON contracts.author_id = authors.id
LEFT JOIN payments ON payments.contract_id = contracts.id
```

Finally, the {b`#[Tempest\Database\BelongsToMany]`} attribute defines a many-to-many relationship using a pivot table. Both sides of the relationship can declare the attribute.

```php
use Tempest\Database\BelongsToMany;

final class Author
{
    /** @var \App\Tag\Tag[] */
    #[BelongsToMany]
    public array $tags = [];
}

final class Tag
{
    /** @var \App\Author\Author[] */
    #[BelongsToMany]
    public array $authors = [];
}
```

The pivot table name is inferred alphabetically from both model table names (e.g., `authors` + `tags` = `authors_tags`). This generates SQL like:

```sql
LEFT JOIN authors_tags ON authors_tags.author_id = authors.id
LEFT JOIN tags ON tags.id = authors_tags.tag_id
```

Of course, there's a lot more you can do with these attributes to make them work exactly as you want. You can [find out all the details in the docs](/3.x/essentials/database#has-one-through).

---

<!-- source: src/Web/Blog/articles/2026-06-05-tempest-markdown.md -->

---

title: A new Markdown parser
description: Introducing tempest/markdown, its design goals, and how it works
tag: thoughts
author: brent

---

What started as a performance experiment ended as a new package: `tempest/markdown`. I read [this post on Reddit](https://www.reddit.com/r/PHP/comments/1tac5j9/mdparser_030_native_php_commonmark_gfm_parser/) about how someone built a Markdown parser as a PHP extension. They mentioned how much faster it was compared to `league/commonmark`, which was the biggest selling point.

Now, I do a lot with Markdown: from blogs to docs, from mails to books, most of the things I do online involve parsing Markdown in some way. And for as long as I can remember, I've used `league/commonmark` to do so. Indeed, it's not the fastest thing out there — but it's manageable. However, with the [100-million-row challenge](/challenges/parsing-100m-lines) still fresh on my mind, I wondered if we really needed an _extension_ to get better Markdown performance. Having used League's implementation for years, I know they heavily rely on regex; which I learned with the 100-million-row challenge, was never the most performant solution for parsing big blobs of text.

So I set up a naive test: a very basic Markdown parser that doesn't rely on regex but instead does a single pass over the text input, translates Markdown into tokens, which are then rendered to HTML. It's not a full-fledged lexer/parser that builds an AST, but instead directly goes from tokens to HTML. After a couple of hours, I got a working prototype. Then I set up [phpbench](https://github.com/phpbench/phpbench) to compare my implementation with league's.

| Package           | Memory   | Time to parse |
| ----------------- | -------- | ------------- |
| tempest/markdown  | 5.944mb  | 6.281ms       |
| league/commonmark | 21.114mb | 56.993ms      |

Of course, my implementation was far from feature-complete, so I figured these numbers weren't accurate yet. However, the difference did show that there might be something to improve, and that a non-regex approach may indeed be faster.

I did wonder whether I missed something obvious, though. The difference in performance was pretty big, and I hadn't even tried that hard. So I did the most productive thing I could think of to verify whether an idea has merit: [I asked /r/php to roast my code](https://www.reddit.com/r/PHP/comments/1tbyepk/roast_my_code_im_building_a_markdown_parser/). The feedback was very valuable, but what stood out most was someone sending a PR to the repo with ["some performance improvements"](https://github.com/tempestphp/markdown/pull/3):

| Package               | Time to parse |
| --------------------- | ------------- |
| tempest/markdown      | 6.281ms       |
| tempest/markdown (PR) | 0.723ms       |
| league/commonmark     | 56.993ms      |

Well that, I did not expect. 0.723ms to parse the Tempest docs in PHP compared to 56.993ms with `league/commonmark`. That's an 80x improvement — give or take; all with PHP. There was a catch, though: the PR did two things: it merged the tokenization and parsing steps into one; but it also removed all tokenizer rule classes (each class representing a specific Markdown token); and merged them into inline functions.

The inline function approach worked, but it made it virtually impossible to add extension points, something I was considering whether it would be worth adding. See, having worked on this code for a couple of days by now, I wondered whether it could actually benefit me for real. Better performance is always good, but we're talking only about a tens of milliseconds difference. `league/commonmark` can definitely feel sluggish at times, but in production these rendered Markdown files are always cached anyway, so it's definitely not the end of the world.

What bothered me more with `league/commonmark` is the fact that it's so bare-bones. Every project I start I have to copy over configuration to support frontmatter, code highlighting, responsive images, tables, external hyperlinks, and what not. There are solutions for all these problems, but `league/commonmark` was designed to be extended, so it takes some setting up and tweaking before I can use it for my use cases.

If I had this Markdown parser that 5-10x faster, with all these features built-in; maybe that wouldn't be so bad?

I so I did exactly that; I continued to add the base Markdown features, and then I added support for all the things _I_ would find useful: frontmatter, code highlighting, responsive images, tables, external hyperlinks, divs, and strikethrough formatting. In the end, the benchmarks showed these results:

| Package           | Memory   | Time to parse |
| ----------------- | -------- | ------------- |
| tempest/markdown  | 6.664mb  | 10.906ms      |
| league/commonmark | 21.114mb | 56.993ms      |

As expected, performance had decreased a bit, but `tempest/markdown` was still 5x faster than `league/commonmark`. I actually suspect there are some big gains to be made still by combining the parsing and HTML rendering in one loop instead of two (TBD).

On top of that, I did add extension points so that external projects could completely change the parser's working to their needs.

So that's where I'm at today. Once again I wonder: what's the next step? And once again, I think it's time to ask /r/php and other places to take another look at what's here. I'm now using the parser myself for my blog and this website. It works very well, it has simplified a lot of code, and I'm happy with it. But is there really something here? I hope others can help me figure that out.

So if you're curious, head over to [the docs](/docs/packages/markdown) and take a look. I'm very open for feedback! (The best place for that feedback would be on [GitHub](https://github.com/tempestphp/markdown), by the way.)

## Why not … ?

As a closing remark: I am anticipating people asking why I don't contribute to `league/commonmark` instead; why I have to write something new.

Well the two obvious reasons are that `league/commonmark` is a regex-based parser by design, and that's not something you just _change_; also it seems to be designed to only follow the official spec, and leave extension points to the community. The two design goals of `tempest/markdown` seem to be diametrically opposed to `league/commonmark`. That's not to say there's anything wrong with one approach or the other, but they are so different that I don't see any way of them working together.

## In closing

Let me know your thoughts! Either on [GitHub](https://github.com/tempestphp/markdown) or on [the Tempest Discord](/discord), or whever you're reading this. I'm looking forward to it!

---

<!-- source: src/Web/hello.md -->

- [Getting Started with Tempest](https://tempestphp.com/3.x/getting-started/introduction) — this is the introduction to our documentation.
- [The Tempest livestream playlist](https://www.youtube.com/playlist?list=PL0bgkxUS9EaILnUL8Q4np6B3qxjQbE7PH) — this is where it started.
- [Building a framework](https://stitcher.io/blog/building-a-framework) — even more origin story.
- [Discovery Explained](https://tempestphp.com/blog/discovery-explained) — a gentle introduction to the one feature that's powering the whole of Tempest.
- [Why I'm pushing for the latest PHP version only](https://stitcher.io/blog/php-84-at-least) — a core principle of Tempest.
- [Console Commands explained](https://tempestphp.com/3.x/essentials/console-commands)
- [Tempest View explained](https://tempestphp.com/3.x/essentials/views)
- [Tempest's ORM](https://tempestphp.com/3.x/essentials/database#models)
- [Configuration in Tempest projects](https://tempestphp.com/3.x/essentials/configuration)
- [The mapper explained](https://tempestphp.com/3.x/features/mapper)
- [Static pages with Tempest](https://tempestphp.com/3.x/features/static-pages)
- [TypeScript with Tempest](https://tempestphp.com/3.x/features/typescript)
- [Tempest on GitHub](/github) — you're always welcome to take a look at what we're working on, and are free to pitch in.
- [Join our Discord!](/discord) — we're a community of around a thousand developers passionate about PHP and web development.

---

<!-- source: src/Web/Homepage/codeblocks/config.md -->

```php src/sqlite.config.php
return new SQLiteConfig(
    path: env('DB_PATH', __DIR__ . '/../database.sqlite'),
);
```

---

<!-- source: src/Web/Homepage/codeblocks/console.md -->

```php src/Books/FetchBookCommand.php
final readonly class FetchBookCommand
{
    public function __construct(
        private BookRepository $repository,
        private Isbn $isbn,
        private Console $console,
    ) {}

    #[ConsoleCommand(description: 'Synchronize a book from ISBN by its title')]
    public function __invoke(string $title, bool $force = false): void
    {
        $data = $this->isbn->findByTitle($title);

        if (! $data) {
            $this->console->error("No book found matching that title.");
            return;
        }

        $book = map($data)->to(Book::class);

        if ($this->repository->exists($book->isbn13) && ! $force) {
            $this->console->info("Book already exists.");
            return;
        }

        $this->repository->save($book);
        $this->console->success("Synchronized {$book->title}.");
    }
}
```

---

<!-- source: src/Web/Homepage/codeblocks/controller.md -->

```php src/Books/BookController.php
final readonly class BookController
{
    #[Post('/books')]
    public function store(CreateBookRequest $request): Response
    {
        $book = map($request)->to(Book::class)->save();

        return new Redirect(uri([self::class, 'show'], book: $book->id));
    }
}
```

---

<!-- source: src/Web/Homepage/codeblocks/event-handler.md -->

```php src/Books/BookObserver.php
final readonly class BookObserver
{
    #[EventHandler]
    public function onBookPublished(BookPublished $event): void
    {
        // …
    }
}
```

---

<!-- source: src/Web/Homepage/codeblocks/initializer.md -->

```php src/Blog/MarkdownInitializer.php
final readonly class MarkdownInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): MarkdownConverter
    {
        $highlighter = new Highlighter(new CssTheme())
            ->addLanguage(new TempestViewLanguage());

        $environment = new Environment()
            ->addRenderer(Code::class, new CodeBlockRenderer($highlighter));

        return new MarkdownConverter($environment);
    }
}
```

---

<!-- source: src/Web/Homepage/codeblocks/mapper.md -->

```php
use function Tempest\map;

map('path/to/books.json')->collection->to(Book::class);

map($book)->to(MapTo::JSON);
```

---

<!-- source: src/Web/Homepage/codeblocks/model.md -->

```php src/Books/Book.php
final class Book
{
    #[Length(min: 1, max: 120)]
    public string $title;

    public ?Author $author = null;

    /** @var \App\Books\Chapter[] */
    public array $chapters = [];
}
```

---

<!-- source: src/Web/Homepage/codeblocks/orm.md -->

```php
$book = query(Book::class)
    ->select()
    ->where('title', 'Timeline Taxi')
    ->first();

// …

$json = map($book)->toJson();
```

---

<!-- source: src/Web/Homepage/codeblocks/query.md -->

```php
query('authors')
    ->insert(...$rows)
    ->execute();
```

---

<!-- source: src/Web/Homepage/codeblocks/static-pages.md -->

```console >_ ./tempest static:generate
/framework/01-getting-started <dim>..</dim> <em>/public/framework/01-getting-started/index.html</em>
/framework/02-the-container <dim>......</dim> <em>/public/framework/02-the-container/index.html</em>
/framework/03-controllers <dim>..........</dim> <em>/public/framework/03-controllers/index.html</em>
/framework/04-views <dim>......................</dim> <em>/public/framework/04-views/index.html</em>
/framework/05-models <dim>....................</dim> <em>/public/framework/05-models/index.html</em>
```

---

<!-- source: src/Web/Homepage/codeblocks/templating-component.md -->

```html src/x-base.view.php
<!DOCTYPE html>
<html lang="en" class="h-dvh flex flex-col">
  <head>
    <!-- Conditional elements -->
    <title :if="isset($title)">{{ $title }} — Books</title>
    <title :else>Books</title>
    <!-- Built-in Vite integration -->
    <x-vite-tags />
  </head>
  <body class="antialiased flex flex-col grow">
    <x-slot />
    <!-- Main slot -->
    <x-slot name="scripts" />
    <!-- Named slot -->
  </body>
</html>
```

---

<!-- source: src/Web/Homepage/codeblocks/templating-view.md -->

```html src/Books/index.view.php
<x-base :title="$this->seo->title">
  <ul>
    <li :foreach="$this->books as $book">
      <!-- Title -->
      <span>{{ $book->title }}</span>

      <!-- Metadata -->
      <span :if="$this->showDate($book)">
        <x-badge variant="outline"> {{ $book->publishedAt }} </x-badge>
      </span>
    </li>
  </ul>
</x-base>
```

---

<!-- source: src/Web/Homepage/codeblocks/view-component.md -->

```html src/Books/x-book.view.php
<article>
  <h1>{{ $book->title }}</h1>
  {!! $book->body !!}
</article>
```

---

<!-- source: src/Web/Homepage/codeblocks/view-processor.md -->

```php
final class StarCountViewProcessor implements ViewProcessor
{
    public function __construct(
        private readonly GitHub $github,
    ) {}

    public function process(View $view): View
    {
        if (! $view instanceof WithStarCount) {
            return $view;
        }

        return $view->data(starCount: $this->github->getStarCount());
    }
}
```

---
