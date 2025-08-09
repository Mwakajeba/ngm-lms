<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

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
     * Accessor to get the count of loans for this group.
     * 
     */
    public function getLoansCountAttribute()
    {
        return $this->loans()->count();
    }
    /**
     * Get the branch for this group.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'group_members', 'group_id', 'customer_id');
    }


    /**
     * Get the group leader (user) for this group.
     */
    public function groupLeader()
    {
        return $this->belongsTo(Customer::class, 'group_leader');
    }

    /**
     * Get the members of this group.
     */
    public function members()
    {
        return $this->hasMany(GroupMember::class);
    }

    /**
     * Get the active members of this group.
     */
    public function activeMembers()
    {
        return $this->hasMany(GroupMember::class)->where('status', 'active');
    }

    /**
     * Get the loans associated with this group.
     */
    // TODO: Uncomment when Loan model is properly set up
    // public function loans()
    // {
    //     return $this->hasMany(Loan::class);
    // }

    /**
     * Get the current member count.
     */
    public function getCurrentMemberCountAttribute()
    {
        return $this->activeMembers()->count();
    }

    /**
     * Get the current member count.
     */
    public function getCurrentMemberCount()
    {
        return $this->activeMembers()->count();
    }

    /**
     * Check if group can accept more members.
     */
    public function canAcceptMoreMembers()
    {
        return $this->getCurrentMemberCount() < $this->maximum_members;
    }

    /**
     * Check if group has minimum required members.
     */
    public function hasMinimumMembers()
    {
        return $this->getCurrentMemberCount() >= $this->minimum_members;
    }

    /**
     * Check if group has a valid meeting schedule.
     */
    public function hasValidMeetingSchedule()
    {
        return !empty($this->meeting_day) && !empty($this->meeting_time);
    }

    /**
     * Check if group has a leader assigned.
     */
    public function hasLeader()
    {
        return !empty($this->group_leader);
    }

    /**
     * Check if group is at maximum capacity.
     */
    public function isAtMaxCapacity()
    {
        return $this->getCurrentMemberCount() >= $this->maximum_members;
    }

    /**
     * Get the status of the group based on member count.
     */
    public function getStatus()
    {
        if ($this->getCurrentMemberCount() < $this->minimum_members) {
            return 'Incomplete';
        } elseif ($this->isAtMaxCapacity()) {
            return 'Full';
        } else {
            return 'Active';
        }
    }
}
