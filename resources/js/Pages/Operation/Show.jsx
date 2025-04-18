import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import {
    Box,
    Typography,
    Paper,
    Grid,
    Button,
    Divider,
    Chip,
} from '@mui/material';
import ArrowBackIcon from '@mui/icons-material/ArrowBack';
import EditIcon from '@mui/icons-material/Edit';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import CancelIcon from '@mui/icons-material/Cancel';

export default function Show({ operation, auth }) {
    return (
        <AdminLayout
            user={auth.user}
            header={
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                    <Link href={route('operation.index')}>
                        <Button
                            variant="outlined"
                            startIcon={<ArrowBackIcon />}
                        >
                            Retour
                        </Button>
                    </Link>
                    <Typography variant="h4" component="h1">
                        Opération #{operation.id}
                    </Typography>
                </Box>
            }
        >
            <Head title={`Opération #${operation.id}`} />

            <Box sx={{ display: 'flex', justifyContent: 'flex-end', mb: 3 }}>
                <Box sx={{ display: 'flex', gap: 2 }}>
                    {operation.statut === 'en_attente' && (
                        <Link href={route('operation.edit', operation.id)}>
                            <Button
                                variant="contained"
                                startIcon={<EditIcon />}
                            >
                                Modifier
                            </Button>
                        </Link>
                    )}
                    {operation.statut === 'en_attente' && (
                        <Link href={route('operation.validate', operation.id)}>
                            <Button
                                variant="contained"
                                color="success"
                                startIcon={<CheckCircleIcon />}
                            >
                                Valider
                            </Button>
                        </Link>
                    )}
                    {operation.statut === 'en_attente' && (
                        <Link href={route('operation.reject', operation.id)}>
                            <Button
                                variant="contained"
                                color="error"
                                startIcon={<CancelIcon />}
                            >
                                Rejeter
                            </Button>
                        </Link>
                    )}
                </Box>
            </Box>

            <Paper sx={{ p: 3 }}>
                <Grid container spacing={3}>
                    <Grid item xs={12}>
                        <Typography variant="h6" gutterBottom>
                            Informations générales
                        </Typography>
                        <Divider sx={{ mb: 2 }} />
                        <Grid container spacing={2}>
                            <Grid item xs={12} sm={6}>
                                <Typography variant="subtitle2" color="text.secondary">
                                    Rubrique
                                </Typography>
                                <Typography variant="body1">
                                    {operation.rubrique?.nom}
                                </Typography>
                            </Grid>
                            <Grid item xs={12} sm={6}>
                                <Typography variant="subtitle2" color="text.secondary">
                                    Date d'arrêt
                                </Typography>
                                <Typography variant="body1">
                                    {operation.date_arret}
                                </Typography>
                            </Grid>
                            <Grid item xs={12} sm={6}>
                                <Typography variant="subtitle2" color="text.secondary">
                                    Statut
                                </Typography>
                                <Chip
                                    label={operation.statut}
                                    color={
                                        operation.statut === 'validee'
                                            ? 'success'
                                            : operation.statut === 'rejetee'
                                            ? 'error'
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
                                    {operation.declarant.name}
                                </Typography>
                            </Grid>
                            <Grid item xs={12} sm={6}>
                                <Typography variant="subtitle2" color="text.secondary">
                                    Valideur
                                </Typography>
                                <Typography variant="body1">
                                    {operation.valideur?.name || '-'}
                                </Typography>
                            </Grid>
                        </Grid>
                    </Grid>

                    {/* <Grid item xs={12}>
                        <Typography variant="h6" gutterBottom>
                            Données
                        </Typography>
                        <Divider sx={{ mb: 2 }} />
                        <Grid container spacing={2}>
                            {Object.entries(operation.donnees).map(([key, value]) => (
                                <Grid item xs={12} sm={6} key={key}>
                                    <Typography variant="subtitle2" color="text.secondary">
                                        {key}
                                    </Typography>
                                    <Typography variant="body1">
                                        {value}
                                    </Typography>
                                </Grid>
                            ))}
                        </Grid>
                    </Grid> */}
                </Grid>
            </Paper>
        </AdminLayout>
    );
} 