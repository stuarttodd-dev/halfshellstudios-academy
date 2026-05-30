# Chapter project: write your app one-pager (features, plugins, platforms)

| Lesson | Use |
| ------ | --- |
| [Chapter project: write your app one-pager](https://docker.learnio.dev/learn/sections/chapter-introduction-and-mobile-architecture/chapter-project-write-your-app-one-pager-features-plugins-platforms) | Copy [`app-one-pager.template.md`](app-one-pager.template.md) and fill it in yourself |
| Solution (this folder) | [`app-one-pager.md`](app-one-pager.md) — reference capstone for the **Field Notes** app |

Lesson **1.16** in chapter 1 of [Using Native PHP](https://docker.learnio.dev/). You are not writing code yet — you are defining the app you will build through chapter 16.

## The loop

```text
Define features → pick plugins → choose platforms → draft one-pager → review → iterate
```

## Exercise

1. Copy the template:

   ```bash
   cp app-one-pager.template.md ~/field-notes-one-pager.md
   ```

2. Fill in **Features**, **Plugins**, and **Platforms** for your own app (or adapt the course capstone).
3. Add at least three **non-goals** so v1 scope stays shippable.
4. Complete the checklist at the bottom of the template before comparing to the solution.

Constraints from the lesson:

- Target **iOS and/or Android** via NativePHP Mobile v3 (not a server-hosted web app).
- List plugins by **Composer package name** and type (official / community / custom).
- Every feature should map to something you can demo on device by the end of the course.

## Solution

Reference one-pager for the course capstone app **Field Notes** (offline-first notes with sync, camera, biometrics, EDGE shell):

- [`app-one-pager.md`](app-one-pager.md)

Compare your plugin choices and platform matrix to the reference. Your app name and feature set can differ; structure and specificity should match.

## Checklist (from the lesson)

- [ ] Features list explains *what problem each feature solves*
- [ ] Plugins table names real packages from the [NativePHP marketplace](https://nativephp.com/plugins/marketplace)
- [ ] Platforms table states minimum OS versions and which devices you will test on
- [ ] Non-goals section exists (scope control)
- [ ] One-sentence debug ladder for when UI works but native behaviour does not

## What's in this folder

| Item | Purpose |
| ---- | ------- |
| [`app-one-pager.template.md`](app-one-pager.template.md) | Blank starter for the exercise |
| [`app-one-pager.md`](app-one-pager.md) | Solution: course capstone **Field Notes** one-pager |

## Related

| Lesson | Topic |
| ------ | ----- |
| [Course capstone app: what you will ship](https://docker.learnio.dev/learn/sections/chapter-introduction-and-mobile-architecture/course-capstone-app-what-you-will-ship) | High-level capstone features |
| [The plugin model](https://docker.learnio.dev/learn/sections/chapter-introduction-and-mobile-architecture/the-plugin-model-official-community-custom) | Official vs community vs custom |
| [Offline-first from one codebase](https://docker.learnio.dev/learn/sections/chapter-introduction-and-mobile-architecture/offline-first-and-cross-platform-from-one-codebase) | `OfflineNote` pattern |

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| Feature list is a wish list | Keep only what you can demo in 17 chapters; move the rest to non-goals |
| Plugin names are vague ("camera plugin") | Open the marketplace page and copy the exact `composer require` package name |
| Unsure which platforms | If you only have an iPhone, ship iOS v1 and mark Android as "parity pass before ch 16" |
| One-pager feels too short | Add a success-criteria checklist and architecture one-liner — length comes from clarity, not padding |

← [Chapter 1 — Introduction and mobile architecture](../README.md)
