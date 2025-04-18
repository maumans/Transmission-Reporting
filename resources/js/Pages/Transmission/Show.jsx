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
    CircularProgress,
    Snackbar,
    Alert,
    Dialog,
    DialogTitle,
    DialogContent,
    DialogActions,
    Table,
    TableBody,
    TableCell,
    TableContainer,
    TableHead,
    TableRow,
    IconButton,
    Tooltip,
} from '@mui/material';
import ArrowBackIcon from '@mui/icons-material/ArrowBack';
import EditIcon from '@mui/icons-material/Edit';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import CancelIcon from '@mui/icons-material/Cancel';
import Send from '@mui/icons-material/Send';
import CalculateIcon from '@mui/icons-material/Calculate';
import PrintIcon from '@mui/icons-material/Print';
import { ArrowBack, DateRange } from '@mui/icons-material';
import dayjs from 'dayjs';
import TransmissionProgress from '@/Components/TransmissionProgress';

export default function Show({ transmission, auth }) {
    const [isLoading, setIsLoading] = useState(false);
    const [showConfirmation, setShowConfirmation] = useState(false);
    const [snackbar, setSnackbar] = useState({
        open: false,
        message: '',
        severity: 'success'
    });

    const handleTransmission = () => {
        setShowConfirmation(true);
    };

    const handleConfirmTransmission = () => {
        setIsLoading(true);
        setShowConfirmation(false);

        router.get(route('transmission.transmit', transmission.id), {}, {
            onSuccess: () => {
                setSnackbar({
                    open: true,
                    message: 'Transmission effectuée avec succès',
                    severity: 'success'
                });
                setIsLoading(false);
            },
            onError: (errors) => {
                setSnackbar({
                    open: true,
                    message: 'Une erreur est survenue lors de la transmission',
                    severity: 'error'
                });
                setIsLoading(false);
            }
        });
    };

    const handleCloseSnackbar = () => {
        setSnackbar(prev => ({ ...prev, open: false }));
    };

    const handleCalculate = (rubrique) => {
        router.get(route('transmission.calculate', { transmission: transmission.id, rubrique: rubrique.id }), {
            onSuccess: () => {
                setSnackbar({
                    open: true,
                    message: 'Calcul effectué avec succès',
                    severity: 'success'
                });
            },
            onError: (errors) => {
                setSnackbar({
                    open: true,
                    message: 'Une erreur est survenue lors du calcul',
                    severity: 'error'
                });
            }
        });
    };

    const handlePrint = (operation) => {
        // Logique pour l'impression
        console.log('Impression pour l\'opération:', operation);
    };

    return (
        <AdminLayout
            user={auth.user}
            header={
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                    <Link href={route('transmission.index')}>
                        <Button
                            variant="outlined"
                            startIcon={<ArrowBack />}
                        >
                            Retour
                        </Button>
                    </Link>
                    <Typography variant="h5" component="h1">
                        Transmission <DateRange /> {dayjs(transmission.date_arretee).format('YYYY-MM-DD')}
                    </Typography>
                </Box>
            }
        >
            <Head title={`Transmission #${dayjs(transmission.date_arretee).format('YYYY-MM-DD')}`} />

            <Box sx={{ display: 'flex', justifyContent: 'flex-end', mb: 3 }}>
                <Box sx={{ display: 'flex', gap: 2 }}>
                    {transmission.etat === 'en_attente' && (
                        <Link href={route('transmission.edit', transmission.id)}>
                            <Button
                                variant="contained"
                                startIcon={<EditIcon />}
                            >
                                Modifier
                            </Button>
                        </Link>
                    )}
                    {transmission.etat === 'en_attente' && (
                        <Link href={route('transmission.validate', transmission.id)}>
                            <Button
                                variant="contained"
                                color="success"
                                startIcon={<CheckCircleIcon />}
                            >
                                Valider
                            </Button>
                        </Link>
                    )}
                    {transmission.etat === 'en_attente' && (
                        <Link href={route('transmission.reject', transmission.id)}>
                            <Button
                                variant="contained"
                                color="error"
                                startIcon={<CancelIcon />}
                            >
                                Rejeter
                            </Button>
                        </Link>
                    )}
                    {transmission.etat === 'validee' && (
                        <Button
                            variant="contained"
                            color="success"
                            startIcon={isLoading ? <CircularProgress size={20} color="inherit" /> : <Send />}
                            onClick={handleTransmission}
                            disabled={transmission.etat === 'transmise' || isLoading}
                        >
                            {isLoading ? 'Transmission en cours...' : 'Transmettre'}
                        </Button>
                    )}
                </Box>
            </Box>

            <Paper sx={{ p: 3, mb: 3 }}>
                <Grid container spacing={3}>
                    <Grid item xs={12}>
                        <Typography variant="h6" gutterBottom>
                            Informations générales
                        </Typography>
                        <Divider sx={{ mb: 2 }} />
                        <Grid container spacing={2}>
                            <Grid item xs={12} sm={6}>
                                <Typography variant="subtitle2" color="text.secondary">
                                    Date d'arrêt
                                </Typography>
                                <Typography variant="body1">
                                    {dayjs(transmission.date_arretee).format('YYYY-MM-DD')}
                                </Typography>
                            </Grid>
                            <Grid item xs={12} sm={6}>
                                <Typography variant="subtitle2" color="text.secondary">
                                    Etat
                                </Typography>
                                <Chip
                                    label={transmission.etat === "en_attente" ? "En attente" : transmission.etat === "validee" ? "Validée" : transmission.etat === "rejetee" ? "Rejetée" : "Transmise"}
                                    color={
                                        transmission.etat === 'validee'
                                            ? 'success'
                                            : transmission.etat === 'rejetee'
                                                ? 'error'
                                                : transmission.etat === 'transmise'
                                                    ? 'success'
                                                : 'warning'
                                    }
                                />
                            </Grid>
                        </Grid>
                    </Grid>

                    <Grid item xs={12}>
                        <Typography variant="h6" gutterBottom>
                            Personnes concernées
                        </Typography>
                        <Divider sx={{ mb: 2 }} />
                        <Grid container spacing={2}>
                            <Grid item xs={12} sm={6}>
                                <Typography variant="subtitle2" color="text.secondary">
                                    Déclarant
                                </Typography>
                                <Typography variant="body1">
                                    {transmission.declarant?.name}
                                </Typography>
                            </Grid>
                            <Grid item xs={12} sm={6}>
                                <Typography variant="subtitle2" color="text.secondary">
                                    Valideur
                                </Typography>
                                <Typography variant="body1">
                                    {transmission.valideur?.name || '-'}
                                </Typography>
                            </Grid>
                        </Grid>
                    </Grid>
                </Grid>
            </Paper>

            {/* Tableau des opérations */}
            {
                transmission.etat === "transmise"
                &&
                <Paper sx={{ p: 3 }}>
                    <Typography variant="h6" gutterBottom>
                        Opérations
                    </Typography>
                    <Divider sx={{ mb: 2 }} />
                    <TableContainer>
                        <Table>
                            <TableHead>
                                <TableRow>
                                    <TableCell>Rubrique</TableCell>
                                    <TableCell>Code</TableCell>
                                    <TableCell>Description</TableCell>
                                    <TableCell>État</TableCell>
                                    <TableCell align="right">Actions</TableCell>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {transmission.rubrique?.children?.map((rubrique) => (
                                    <TableRow key={rubrique.id}>
                                        <TableCell>{rubrique?.nom}</TableCell>
                                        <TableCell>{rubrique?.code}</TableCell>
                                        <TableCell>{rubrique?.description}</TableCell>
                                        <TableCell>
                                            <Chip
                                                label={rubrique.etat}
                                                color={
                                                    rubrique.etat === 'validee'
                                                        ? 'success'
                                                        : rubrique.etat === 'rejetee'
                                                            ? 'error'
                                                            : 'warning'
                                                }
                                                size="small"
                                            />
                                        </TableCell>
                                        <TableCell align="right">
                                            <Box sx={{ display: 'flex', justifyContent: 'flex-end', gap: 1 }}>
                                                <Tooltip title="Calculer">
                                                    <IconButton
                                                        color="primary"
                                                        onClick={() => handleCalculate(rubrique)}
                                                        disabled={!rubrique.actif}
                                                    >
                                                        <CalculateIcon />
                                                    </IconButton>
                                                </Tooltip>
                                                <Tooltip title="Imprimer">
                                                    <IconButton
                                                        color="secondary"
                                                        onClick={() => handlePrint(rubrique)}
                                                        disabled={rubrique.etat !== 'validee'}
                                                    >
                                                        <PrintIcon />
                                                    </IconButton>
                                                </Tooltip>
                                            </Box>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </TableContainer>
                </Paper>
            }


            {/* Dialog de confirmation */}
            <Dialog
                open={showConfirmation}
                onClose={() => setShowConfirmation(false)}
            >
                <DialogTitle>Confirmer la transmission</DialogTitle>
                <DialogContent>
                    <Typography>
                        Êtes-vous sûr de vouloir transmettre cette transmission ?
                        Cette action est irréversible.
                    </Typography>
                </DialogContent>
                <DialogActions>
                    <Button onClick={() => setShowConfirmation(false)}>
                        Annuler
                    </Button>
                    <Button
                        onClick={handleConfirmTransmission}
                        color="success"
                        variant="contained"
                        disabled={isLoading}
                    >
                        {isLoading ? (
                            <>
                                <CircularProgress size={20} sx={{ mr: 1 }} />
                                Transmission en cours...
                            </>
                        ) : (
                            'Confirmer'
                        )}
                    </Button>
                </DialogActions>
            </Dialog>

            {/* Snackbar pour les notifications */}
            <Snackbar
                open={snackbar.open}
                autoHideDuration={6000}
                onClose={handleCloseSnackbar}
                anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
            >
                <Alert
                    onClose={handleCloseSnackbar}
                    severity={snackbar.severity}
                    sx={{ width: '100%' }}
                >
                    {snackbar.message}
                </Alert>
            </Snackbar>
        </AdminLayout>
    );
} 