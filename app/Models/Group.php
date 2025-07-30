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
        'minimum_members',
        'maximum_members',
        'group_leader',
        'meeting_day',
        'meeting_time',
    ];

    protected $casts = [
        'meeting_time' => 'datetime:H:i',
    ];

    /**
     * Get the loan officer (user) for this group.
     */
    public function loanOfficer()
    {
        return $this->belongsTo(User::class, 'loan_officer');
    }

    /**
     * Get the group leader (user) for this group.
     */
    public function groupLeader()
    {
        return $this->belongsTo(User::class, 'group_leader');
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
     * Check if group can accept more members.
     */
    public function canAcceptMoreMembers()
    {
        return $this->current_member_count < $this->maximum_members;
    }

    /**
     * Check if group has minimum required members.
     */
    public function hasMinimumMembers()
    {
        return $this->current_member_count >= $this->minimum_members;
    }
}
