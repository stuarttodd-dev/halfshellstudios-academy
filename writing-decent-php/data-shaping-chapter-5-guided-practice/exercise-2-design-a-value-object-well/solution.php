<?php
declare(strict_types=1);

/**
 * DateRange — half-open interval [startsOn, endsOn).
 *
 * Conventions:
 *  - immutable: every "modifier" returns a new instance
 *  - half-open: contains() is true when startsOn <= $date < endsOn
 *  - lengthInDays() is the count of full days the range covers, so
 *    a single-day range like [Mon 00:00, Tue 00:00) has length 1
 *  - the constructor enforces endsOn strictly after startsOn — empty
 *    or backwards ranges are not representable
 */
final class DateRange
{
    public function __construct(
        public readonly DateTimeImmutable $startsOn,
        public readonly DateTimeImmutable $endsOn,
    ) {
        if ($endsOn <= $startsOn) {
            throw new InvalidArgumentException(
                'DateRange endsOn must be strictly after startsOn.'
            );
        }
    }

    public static function forSingleDay(DateTimeImmutable $date): self
    {
        $start = $date->setTime(0, 0, 0);

        return new self($start, $start->modify('+1 day'));
    }

    public static function forCalendarMonth(DateTimeImmutable $anyDayInMonth): self
    {
        $start = $anyDayInMonth->modify('first day of this month')->setTime(0, 0, 0);

        return new self($start, $start->modify('+1 month'));
    }

    public function contains(DateTimeImmutable $date): bool
    {
        return $date >= $this->startsOn && $date < $this->endsOn;
    }

    public function lengthInDays(): int
    {
        return (int) $this->startsOn->diff($this->endsOn)->days;
    }

    public function overlapsWith(self $other): bool
    {
        return $this->startsOn < $other->endsOn
            && $other->startsOn < $this->endsOn;
    }

    public function extendedBy(int $days): self
    {
        if ($days < 0) {
            throw new InvalidArgumentException('extendedBy() expects a non-negative number of days.');
        }

        return new self(
            $this->startsOn,
            $this->endsOn->modify("+{$days} day"),
        );
    }
}

/* ---------- demo / informal smoke check ---------- */

$jan = new DateRange(
    new DateTimeImmutable('2026-01-01 00:00:00'),
    new DateTimeImmutable('2026-02-01 00:00:00'),
);

$lateJan = new DateRange(
    new DateTimeImmutable('2026-01-20 00:00:00'),
    new DateTimeImmutable('2026-02-10 00:00:00'),
);

$singleDay   = DateRange::forSingleDay(new DateTimeImmutable('2026-03-15 09:30:00'));
$marchMonth  = DateRange::forCalendarMonth(new DateTimeImmutable('2026-03-15 09:30:00'));

printf("january length:        %d days\n",       $jan->lengthInDays());
printf("single day length:     %d day(s)\n",     $singleDay->lengthInDays());
printf("march length:          %d days\n",       $marchMonth->lengthInDays());
printf("jan contains 15th:     %s\n",            $jan->contains(new DateTimeImmutable('2026-01-15')) ? 'true' : 'false');
printf("jan contains feb 1st:  %s\n",            $jan->contains(new DateTimeImmutable('2026-02-01')) ? 'true' : 'false');
printf("jan overlaps lateJan:  %s\n",            $jan->overlapsWith($lateJan)   ? 'true' : 'false');
printf("jan overlaps march:    %s\n",            $jan->overlapsWith($marchMonth) ? 'true' : 'false');
printf("extendedBy(7) length:  %d days (was %d)\n", $jan->extendedBy(7)->lengthInDays(), $jan->lengthInDays());

try {
    new DateRange(new DateTimeImmutable('2026-01-10'), new DateTimeImmutable('2026-01-10'));
} catch (InvalidArgumentException $e) {
    printf("invariant fired:       %s\n", $e->getMessage());
}
