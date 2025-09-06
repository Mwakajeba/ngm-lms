<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory,LogsActivity;

    protected $fillable = [
        'name',
        'loan_officer',
        'branch_id',
        'minimum_members',
        'maximum_members',
        'group_leader',
        'meeting_day',
        'meeting_time',
    ];

    protected $casts = [
        'meeting_time' => 'datetime:H:i',
        'minimum_members' => 'integer',
        'maximum_members' => 'integer',
        'group_leader' => 'integer',
        'loan_officer' => 'integer',
        'branch_id' => 'integer',
    ];

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Get the loan officer (user) for this group.
     */
    public function loanOfficer()
    {
        return $this->belongsTo(User::class, 'loan_officer');
    }

    /**
     * Get the branch for this group.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the group leader (customer) for this group.
     */
    public function groupLeader()
    {
        return $this->belongsTo(Customer::class, 'group_leader');
    }

    /**
     * Accessor to get the count of loans for this group.
     * 
     */
    public function getLoansCountAttribute()
    {
        return $this->loans()->count();
    }

    /**
     * Get the members of this group.
     */
    public function members()
    {
        return $this->belongsToMany(Customer::class, 'group_members', 'group_id', 'customer_id');
    }

    /**
     * Get the count of members in this group.
     */
    public function getMembersCountAttribute()
    {
        return $this->members()->count();
    }

    /**
     * Check if the group has reached its maximum member limit.
     */
    public function hasReachedMaxMembers()
    {
        if (!$this->maximum_members) {
            return false;
        }
        
        return $this->members_count >= $this->maximum_members;
    }

    /**
     * Check if the group has reached its minimum member requirement.
     */
    public function hasReachedMinMembers()
    {
        if (!$this->minimum_members) {
            return true;
        }
        
        return $this->members_count >= $this->minimum_members;
    }

    /**
     * Get the next meeting date based on meeting_day and meeting_time.
     */
    public function getNextMeetingDate()
    {
        if (!$this->meeting_day || !$this->meeting_time) {
            return null;
        }

        $today = now();
        $meetingTime = $this->meeting_time;

        switch ($this->meeting_day) {
            case 'every_day':
                $nextMeeting = $today->copy()->setTimeFromTimeString($meetingTime);
                if ($nextMeeting->isPast()) {
                    $nextMeeting->addDay();
                }
                break;
            case 'every_week':
                $nextMeeting = $today->copy()->nextWeekday()->setTimeFromTimeString($meetingTime);
                break;
            case 'every_month':
                $nextMeeting = $today->copy()->addMonth()->setTimeFromTimeString($meetingTime);
                break;
            default:
                $dayOfWeek = strtolower($this->meeting_day);
                $nextMeeting = $today->copy()->next($dayOfWeek)->setTimeFromTimeString($meetingTime);
                break;
        }

        return $nextMeeting;
    }

    /**
     * Scope to filter groups by branch.
     */
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to filter groups by loan officer.
     */
    public function scopeByLoanOfficer($query, $loanOfficerId)
    {
        return $query->where('loan_officer', $loanOfficerId);
    }

    /**
     * Scope to filter groups that have reached minimum members.
     */
    public function scopeWithMinMembers($query)
    {
        return $query->whereHas('members', function ($q) {
            $q->havingRaw('COUNT(*) >= groups.minimum_members');
        });
    }

    /**
     * Scope to filter groups that haven't reached maximum members.
     */
    public function scopeNotMaxMembers($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('maximum_members')
              ->orWhereHas('members', function ($subQ) {
                  $subQ->havingRaw('COUNT(*) < groups.maximum_members');
              });
        });
    }
}
