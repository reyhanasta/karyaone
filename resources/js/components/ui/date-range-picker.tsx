"use client"

import * as React from "react"
import { CalendarIcon, X } from "lucide-react"
import { format, parseISO } from "date-fns"
import { id } from "date-fns/locale"
import type { DateRange } from "react-day-picker"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Calendar } from "@/components/ui/calendar"
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover"

interface DateRangePickerProps {
  className?: string
  startDate?: string
  endDate?: string
  onSelect: (range: { startDate?: string; endDate?: string }) => void
  placeholder?: string
}

export function DateRangePicker({
  className,
  startDate,
  endDate,
  onSelect,
  placeholder = "Pilih Tanggal",
}: DateRangePickerProps) {
  const [date, setDate] = React.useState<DateRange | undefined>(() => {
    if (startDate && endDate) {
      return {
        from: parseISO(startDate),
        to: parseISO(endDate),
      }
    }
    if (startDate) {
      return {
        from: parseISO(startDate),
        to: undefined,
      }
    }
    return undefined
  })

  React.useEffect(() => {
    if (startDate || endDate) {
      setDate({
        from: startDate ? parseISO(startDate) : undefined,
        to: endDate ? parseISO(endDate) : undefined,
      })
    } else {
      setDate(undefined)
    }
  }, [startDate, endDate])

  const handleSelect = (selectedRange: DateRange | undefined) => {
    setDate(selectedRange)
    const formattedStart = selectedRange?.from
      ? format(selectedRange.from, "yyyy-MM-dd")
      : undefined
    const formattedEnd = selectedRange?.to
      ? format(selectedRange.to, "yyyy-MM-dd")
      : undefined

    onSelect({ startDate: formattedStart, endDate: formattedEnd })
  }

  const handleClear = (e: React.MouseEvent) => {
    e.stopPropagation()
    setDate(undefined)
    onSelect({ startDate: undefined, endDate: undefined })
  }

  return (
    <div className={cn("grid gap-2", className)}>
      <Popover>
        <PopoverTrigger asChild>
          <Button
            id="date"
            variant={"outline"}
            className={cn(
              "w-[260px] justify-start text-left font-normal",
              !date && "text-muted-foreground"
            )}
          >
            <CalendarIcon className="mr-2 h-4 w-4" />
            <span className="flex-1 truncate">
              {date?.from ? (
                date.to ? (
                  <>
                    {format(date.from, "dd MMM yyyy", { locale: id })} -{" "}
                    {format(date.to, "dd MMM yyyy", { locale: id })}
                  </>
                ) : (
                  format(date.from, "dd MMM yyyy", { locale: id })
                )
              ) : (
                <span>{placeholder}</span>
              )}
            </span>
            {date?.from && (
              <X
                className="ml-2 h-4 w-4 opacity-50 hover:opacity-100"
                onClick={handleClear}
              />
            )}
          </Button>
        </PopoverTrigger>
        <PopoverContent className="w-auto p-0" align="end">
          <Calendar
            initialFocus
            mode="range"
            defaultMonth={date?.from}
            selected={date}
            onSelect={handleSelect}
            numberOfMonths={2}
          />
        </PopoverContent>
      </Popover>
    </div>
  )
}
