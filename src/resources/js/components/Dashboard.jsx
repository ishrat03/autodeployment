import React, { useEffect, useState } from 'react';

const Dashboard = () => {
    const [deployments, setDeployments] = useState([]);

    // useEffect(() => {
    //     fetch('/api/deployments')
    //         .then(response => response.json())
    //         .then(data => setDeployments(data));
    // }, []);

    return (
        <div>
            <h1>Deployment Dashboard</h1>
            <ul>
                {deployments.map(deployment => (
                    <li key={deployment.id}>
                        {deployment.name} - {deployment.status}
                    </li>
                ))}
            </ul>
        </div>
    );
};

export default Dashboard;
