import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import {
    Box,
    Typography,
    Paper,
    Grid,
    Button,
    Divider,
    Chip,
    Alert,
    Snackbar,
} from '@mui/material';
import ArrowBackIcon from '@mui/icons-material/ArrowBack';
import EditIcon from '@mui/icons-material/Edit';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import CancelIcon from '@mui/icons-material/Cancel';
import { ArrowBack, DateRange, Send } from '@mui/icons-material';
import dayjs from 'dayjs';
import TransmissionProgress from '@/Components/TransmissionProgress';

export default function Show({ balance, auth }) {
    const [isTransmitting, setIsTransmitting] = useState(false);
    const [currentStep, setCurrentStep] = useState(0);
    const [showSuccess, setShowSuccess] = useState(false);
    const [showError, setShowError] = useState(false);
    const [errorMessage, setErrorMessage] = useState('');

    const handleTransmission = async () => {
        setIsTransmitting(true);
        setCurrentStep(0);

        try {
            // Étape 1: Préparation
            setCurrentStep(0);
            await new Promise(resolve => setTimeout(resolve, 1000));

            // Étape 2: Lecture du fichier
            setCurrentStep(1);
            await new Promise(resolve => setTimeout(resolve, 2000));

            // Étape 3: Transmission à l'API
            setCurrentStep(2);
            const response = await router.get(route('balance.transmission', balance.id));

            // Étape 4: Traitement de la réponse
            setCurrentStep(3);
            await new Promise(resolve => setTimeout(resolve, 1000));

            setShowSuccess(true);
            router.reload();
        } catch (error) {
            setErrorMessage(error.message || 'Une erreur est survenue lors de la transmission');
            setShowError(true);
        } finally {
            setIsTransmitting(false);
            setCurrentStep(0);
        }
    };

    return (
        <AdminLayout
            user={auth.user}
            header={
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                    <Link href={route('balance.index')}>
                        <Button
                            variant="outlined"
                            startIcon={<ArrowBack />}
                        >
                            Retour
                        </Button>
                    </Link>
                    <Typography variant="h5" component="h1">
                        Balance <DateRange /> {dayjs(balance.date_arretee).format('YYYY-MM-DD')}
                    </Typography>
                </Box>
            }
        >
            <Head title={`Balance #${dayjs(balance.date_arretee).format('YYYY-MM-DD')}`} />

            <Box sx={{ display: 'flex', justifyContent: 'flex-end', mb: 3 }}>
                <Box sx={{ display: 'flex', gap: 2 }}>
                    {balance.etat === 'en_attente' && (
                        <Link href={route('balance.edit', balance.id)}>
                            <Button
                                variant="contained"
                                startIcon={<EditIcon />}
                            >
                                Modifier
                            </Button>
                        </Link>
                    )}
                    {balance.etat === 'en_attente' && (
                        <Link href={route('balance.validate', balance.id)}>
                            <Button
                                variant="contained"
                                color="success"
                                startIcon={<CheckCircleIcon />}
                            >
                                Valider
                            </Button>
                        </Link>
                    )}
                    {balance.etat === 'en_attente' && (
                        <Link href={route('balance.reject', balance.id)}>
                            <Button
                                variant="contained"
                                color="error"
                                startIcon={<CancelIcon />}
                            >
                                Rejeter
                            </Button>
                        </Link>
                    )}

                    {balance.etat === 'validee' && (
                        <Button
                            variant="contained"
                            color="success"
                            startIcon={<Send />}
                            onClick={handleTransmission}
                            disabled={balance.etat === 'transmise'}
                        >
                            Transmettre
                        </Button>
                    )}
                </Box>
            </Box>

            <Paper sx={{ p: 3 }}>
                <Grid container spacing={3}>
                    <Grid item size={{ xs: 12 }}>
                        <Typography variant="h6" gutterBottom>
                            Informations générales
                        </Typography>
                        <Divider sx={{ mb: 2 }} />
                        <Grid container spacing={2}>
                            <Grid item size={{ xs: 12, sm: 6 }}>
                                <Typography variant="subtitle2" color="text.secondary">
                                    Date d'arrêt
                                </Typography>
                                <Typography variant="body1">
                                    {dayjs(balance.date_arretee).format('YYYY-MM-DD')}
                                </Typography>
                            </Grid>
                            <Grid item size={{ xs: 12, sm: 6 }}>
                                <Typography variant="subtitle2" color="text.secondary">
                                    Etat
                                </Typography>
                                <Chip
                                    label={balance.etat === "en_attente" ? "En attente" : balance.etat === "validee" ? "Validée" : balance.etat === "rejetee" ? "Rejetée" : ""}
                                    color={
                                        balance.etat === 'validee'
                                            ? 'success'
                                            : balance.etat === 'rejetee'
                                                ? 'error'
                                                : 'warning'
                                    }
                                />
                            </Grid>
                        </Grid>
                    </Grid>

                    <Grid item size={{ xs: 12 }}>
                        <Typography variant="h6" gutterBottom>
                            Personnes concernées
                        </Typography>
                        <Divider sx={{ mb: 2 }} />
                        <Grid container spacing={2}>
                            <Grid item size={{ xs: 12, sm: 6 }}>
                                <Typography variant="subtitle2" color="text.secondary">
                                    Déclarant
                                </Typography>
                                <Typography variant="body1">
                                    {balance.declarant?.name}
                                </Typography>
                            </Grid>
                            <Grid item size={{ xs: 12, sm: 6 }}>
                                <Typography variant="subtitle2" color="text.secondary">
                                    Valideur
                                </Typography>
                                <Typography variant="body1">
                                    {balance.valideur?.name || '-'}
                                </Typography>
                            </Grid>
                        </Grid>
                    </Grid>
                </Grid>
            </Paper>

            <Snackbar
                open={showSuccess}
                autoHideDuration={6000}
                onClose={() => setShowSuccess(false)}
            >
                <Alert severity="success" onClose={() => setShowSuccess(false)}>
                    Transmission réussie !
                </Alert>
            </Snackbar>

            <Snackbar
                open={showError}
                autoHideDuration={6000}
                onClose={() => setShowError(false)}
            >
                <Alert severity="error" onClose={() => setShowError(false)}>
                    {errorMessage}
                </Alert>
            </Snackbar>
        </AdminLayout>
    );
} 