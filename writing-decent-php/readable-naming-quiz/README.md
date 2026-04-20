# Chapter 1 quiz — readable naming

Reference answers for the 20-question quiz in **What decent PHP means after fundamentals → Chapter 1 quiz**
(`/learn/sections/chapter-readable-naming/readable-naming-quiz`).

The quiz checks your judgement around the **maintainability mindset** and
**readable code**. The pass mark is **80%** (16 of 20).

## Quick recap

The questions all push the same handful of habits:

- Decide what behaviour must stay the same **before** you change anything.
- Favour small, named improvements over rewrites or new abstractions.
- Treat readability as a feature: the next reader (often you) is the user.
- Watch for **friction**, **change surface**, and **code smells** as signals — not as bugs.
- Reach for a new abstraction only when there is repeated, concrete pressure for it.

## Answers

> Each answer is followed by a one-line rationale, in the same spirit as the chapter:
> prefer the option that improves clarity and change safety without inventing structure
> the code has not earned.

### Q1. Before changing awkward code in Chapter 1, what should you decide first?

**A. What behaviour must stay the same while you clean it up.**

You cannot judge any refactor as "safe" until you have pinned down the behaviour
you are not allowed to break. Everything else is style.

### Q2. Which review comment best matches the Chapter 1 mindset?

**D. This branch is hard to scan; rename the condition and keep the first move small.**

Names a concrete clarity problem and proposes the smallest proportionate fix —
no new files, no design pattern, no rewrite.

### Q3. What does local reasoning mean in this course?

**B. Understanding a piece of code without chasing meaning across the whole project.**

If you have to open six files and remember a global to know what one function
does, local reasoning has been lost.

### Q4. Which option most clearly signals overengineering?

**A. Adding new abstractions before repeated pressure or a clear need exists.**

Abstractions earn their place by removing duplication that is actually hurting
you, not by anticipating a future that may never arrive.

### Q5. Which description best fits maintainability?

**B. Code that is easy to understand, review, change, and hand over safely.**

Maintainability is a property of how the code behaves under change, not a count
of lines or comments.

### Q6. Why does Chapter 1 treat readability as a feature?

**A. Because readable code helps review, debugging, and safe future changes.**

Readability is not decoration — it is the substrate every other engineering
activity stands on.

### Q7. What usually increases maintenance cost fastest?

**C. The same rule duplicated across several files and branches.**

Every duplicate is a place a future change can be missed. One helper or one
short function would be cheaper than three drifting copies.

### Q8. What is change surface?

**B. The amount of code you must inspect or edit when one requirement changes.**

A small change surface is the practical pay-off of decent structure: one
requirement should map to a small, obvious set of edits.

### Q9. What does the "future you" lens encourage?

**C. Making today's code easier for the next reader, including you later.**

Six months from now you will have forgotten the context. Write for that person.

### Q10. In this course, what is a rough feature?

**B. A believable piece of working code that still has clarity or structure problems.**

Rough features are the teaching material — real-shaped code that works but
still has something to improve.

### Q11. What is a code smell in Chapter 1 terms?

**C. A clue that code may be harder to understand or change than it needs to be.**

A smell is a hint to look closer, not a verdict that something is broken.

### Q12. What does friction mean here?

**A. The small repeated reader effort that makes code heavier to understand and change.**

Friction is the death-by-a-thousand-cuts cost of unclear names, tangled
branches, and hidden dependencies.

### Q13. Which choice is the best example of a cleanup step?

**B. Rename one vague function before deciding on larger structural changes.**

One small, reversible improvement at a time. The bigger structural call gets
easier once the code reads more honestly.

### Q14. What does judgement mean in the Chapter 1 framing?

**C. Choosing a proportionate improvement instead of ignoring pressure or overreacting.**

Judgement is the middle path between "leave it alone" and "rewrite the world".

### Q15. Why does the course use a project thread?

**B. So quality decisions build on familiar code instead of disconnected examples.**

Decisions about structure only really land when you have lived with the code
they are improving.

### Q16. What is a course boundary?

**A. A statement of what this course is teaching now and what it is deliberately leaving for later.**

Naming the boundary keeps lessons honest and stops scope from drifting into
unrelated territory.

### Q17. Which first move best fits Chapter 1 when a function has a vague condition and nested branches?

**B. Name the condition clearly and flatten the path if you can do it safely.**

A clear name plus a guard clause buys the most clarity for the smallest risk —
exactly the pattern from the guided practice.

### Q18. What is the strongest sign that a Chapter 1 refactor is staying proportionate?

**A. It improves understanding without inventing extra structure the code has not earned.**

The win is reader effort going down. Adding files or interfaces is a cost, not
proof of progress.

### Q19. When does a maintainability cleanup become risky?

**A. When behaviour changes without safety checks or clear awareness of the impact.**

Cleanups are meant to be behaviour-preserving. The moment behaviour shifts you
need a test, a check, or a deliberate decision — not a quiet drift.

### Q20. What is the best Chapter 1 takeaway?

**B. Prefer small, evidence-based improvements over stylistic rewrites and big design leaps.**

Small, justified moves compound. Big leaps tend to swap one set of problems
for another.

## Answer key

| Q  | Answer | Q  | Answer |
| -- | ------ | -- | ------ |
| 1  | A      | 11 | C      |
| 2  | D      | 12 | A      |
| 3  | B      | 13 | B      |
| 4  | A      | 14 | C      |
| 5  | B      | 15 | B      |
| 6  | A      | 16 | A      |
| 7  | C      | 17 | B      |
| 8  | B      | 18 | A      |
| 9  | C      | 19 | A      |
| 10 | B      | 20 | B      |

20 / 20 = 100% — comfortably above the 80% pass mark.

← [Writing decent PHP](../README.md)
