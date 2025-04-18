import React, { useState, useEffect } from 'react';
import { Box, CircularProgress, Typography, Paper, LinearProgress } from '@mui/material';
import { styled } from '@mui/material/styles';

const ProgressContainer = styled(Paper)(({ theme }) => ({
    padding: theme.spacing(3),
    margin: theme.spacing(2),
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
    gap: theme.spacing(2),
}));

const ProgressInfo = styled(Box)(({ theme }) => ({
    width: '100%',
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
}));

const TransmissionProgress = ({ totalSteps = 3, currentStep = 0, estimatedTime = 30 }) => {
    const [timeRemaining, setTimeRemaining] = useState(estimatedTime);
    const [progress, setProgress] = useState(0);

    useEffect(() => {
        const interval = setInterval(() => {
            setTimeRemaining(prev => {
                if (prev <= 0) return 0;
                return prev - 1;
            });
        }, 1000);

        return () => clearInterval(interval);
    }, []);

    useEffect(() => {
        setProgress((currentStep / totalSteps) * 100);
    }, [currentStep, totalSteps]);

    const getStepLabel = (step) => {
        switch (step) {
            case 0:
                return 'Préparation de la transmission...';
            case 1:
                return 'Lecture du fichier de balance...';
            case 2:
                return 'Transmission à l\'API...';
            case 3:
                return 'Traitement de la réponse...';
            default:
                return 'En cours...';
        }
    };

    return (
        <ProgressContainer elevation={3}>
            <CircularProgress 
                variant="determinate" 
                value={progress} 
                size={80}
                thickness={4}
            />
            
            <Typography variant="h6" gutterBottom>
                {getStepLabel(currentStep)}
            </Typography>

            <Box sx={{ width: '100%' }}>
                <LinearProgress 
                    variant="determinate" 
                    value={progress} 
                    sx={{ height: 10, borderRadius: 5 }}
                />
                <ProgressInfo>
                    <Typography variant="body2" color="text.secondary">
                        {Math.round(progress)}% complété
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                        Temps restant : {timeRemaining}s
                    </Typography>
                </ProgressInfo>
            </Box>
        </ProgressContainer>
    );
};

export default TransmissionProgress; 