import React, { useState } from 'react';
import { toast } from 'sonner';
import { updateUserRole } from '../../api/apiService';

const UserRoleUpdater = ({ user, refreshUsersList }) => {
    const [selectedRole, setSelectedRole] = useState(user.role);
    const [isLoading, setIsLoading] = useState(false);
    
    const roles = ["customer", "provider", "admin"];

    const handleRoleChange = async (e) => {
        const newRole = e.target.value;
        setSelectedRole(newRole);
        setIsLoading(true);

        try {
            await updateUserRole(user._id, { role: newRole });
            toast.success(`Role for ${user.name} updated to ${newRole}.`);
            refreshUsersList(); 
        } catch (err) {
            setSelectedRole(user.role); 
            toast.error(err.response?.data?.message || "Failed to update role. Please try again.");
            console.error(err);
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <select
            value={selectedRole}
            onChange={handleRoleChange}
            disabled={isLoading}
            className="p-2 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-yellow-500 disabled:opacity-70 disabled:cursor-wait">
            {roles.map((role) => (
                <option key={role} value={role}>
                    {role.charAt(0).toUpperCase() + role.slice(1)}
                </option>
            ))}
        </select>
    );
};

export default UserRoleUpdater;